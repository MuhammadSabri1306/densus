<script setup>
import { ref, computed } from "vue";
import { useIkeStore } from "@/stores/ike";
import { useUserStore } from "@/stores/user";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const ikeStore = useIkeStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = ikeStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, index, groupKey) => {
    let count = 0;
    const sum = [];
    let isExists = [];
    const currItem = JSON.parse(JSON.stringify(data[index]));

    data.forEach(dataItem => {
        if(dataItem.location[groupKey] != currItem.location[groupKey])
            return;

        for(let i=0; i<currItem.ike.length; i++) {
            if(sum.length <= i) {
                sum.push(0);
                isExists.push(false);
            }
            sum[i] += Number(dataItem.ike[i].ike_value);
            if(dataItem.ike[i].isExists)
                isExists[i] = true;
        }
        count++;
    });
    
    for(let i=0; i<currItem.ike.length; i++) {
        const ikeValue = count < 1 ? 0 : sum[i] / count;
        currItem.ike[i].ike_value = toFixedNumber(ikeValue, 2);
        currItem.ike[i].isExists = isExists[i];
    }

    return currItem;
};

const hasCollapseInit = ref(false);
const resetCollapse = () => {
    hasCollapseInit.value = false;
    collapsedDivre.value = [];
    collapsedWitel.value = [];
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };

    const result = data.reduce((result, item, index) => {
        let type = null;
        let title = null;

        if(groupKeys.divre !== item.location.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.location.divre_name;
            const divreData = getGroupAvg(data, index, "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.location.divre_kode;

            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.location.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.location.divre_kode);
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            const witelData = getGroupAvg(data, index, "witel_kode");

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.location.witel_kode;

            const hasWitelCollapsed = collapsedWitel.value.indexOf(item.location.witel_kode) >= 0;
            if(!hasCollapseInit.value && !hasWitelCollapsed)
                toggleRowCollapse("witel", item.location.witel_kode);
        }
        
        type = "sto";
        title = item.location.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);

    if(!hasCollapseInit.value)
        hasCollapseInit.value = true;
    return result;
};

const monthGroup = ref([]);
const colMonth = computed(() => {
    const fetchedMonth = monthGroup.value;
    const monthList = ikeStore.monthList;
    const selectedMonth = ikeStore.filters.month;

    if(fetchedMonth.length > 0)
        return fetchedMonth;
    if(!selectedMonth)
        return monthList;
    return [ monthList[selectedMonth - 1] ];
});

const tableData = ref([]);
const setupData = rawDataLocation => {
    const monthList = ikeStore.monthList;
    const selectedMonth = ikeStore.filters.month;
    
    const month = !selectedMonth ? monthList : [ monthList[selectedMonth - 1] ];
    const data = [];

    rawDataLocation.forEach((item, rowIndex) => {

        data.push({ location: item.location, ike: [] });
        const ikeOnLocation = item.ike;
        month.forEach(monthItem => {
            for(let weekNumber=1; weekNumber<=4; weekNumber++) {

                const ikeIndex = ikeOnLocation.findIndex(ikeItem => {
                    if(!ikeItem)
                        return false;
                    const isMonthMatched = Number(ikeItem.month) == monthItem.number;
                    const isWeekMatched = Number(ikeItem.week) == weekNumber;
                    return isMonthMatched && isWeekMatched;
                });
                
                const ikeCol = {
                    month: monthItem.number,
                    week: weekNumber,
                };

                if(ikeIndex < 0) {
                    ikeCol.isExists = false;
                    ikeCol.ike_value = 0;
                } else {
                    ikeCol.isExists = true;
                    ikeCol.ike_value = toFixedNumber(ikeOnLocation[ikeIndex].ike_value, 2);
                    ikeOnLocation.splice(ikeIndex, 1);
                }
                data[rowIndex].ike.push(ikeCol);

            }
        });

    });

    monthGroup.value = month;
    tableData.value = groupData(data);
};

const isLoading = ref(true);
const hasInit = ref(false);
const fetch = () => {
    isLoading.value = true;
    hasInit.value = true;
    ikeStore.getData(({ data }) => {
        if(data.ike_data) {
            resetCollapse();
            setupData(data.ike_data);
        }
        isLoading.value = false;
    });
};

defineExpose({ fetch });

const getRowClass = item => {
    let collapsed = false;
    if(item.type == "sto") {
        const isWitelCollapsed = collapsedWitel.value.indexOf(item.location.witel_kode) >= 0;
        const isDivreCollapsed = collapsedDivre.value.indexOf(item.location.divre_kode) >= 0;
        collapsed = isWitelCollapsed || isDivreCollapsed;
    } else if(item.type == "witel") {
        collapsed = collapsedDivre.value.indexOf(item.location.divre_kode) >= 0;
    }
    
    return { "row-collapsed": collapsed };
};
</script>
<template>
    <div v-if="hasInit">
        <div v-if="isLoading" class="card card-body">
            <div class="row gy-4">
                <div v-for="item in 24" class="col-3 col-lg-4">
                    <Skeleton />
                </div>
            </div>
        </div>
        <div v-else-if="tableData.length < 1" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada lokasi.</h4>
        </div>
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-gepee">
                <thead>
                    <tr>
                        <th rowspan="2" class="bg-success sticky-column">Lingkup Kerja</th>
                        <th v-for="item in colMonth" colspan="4" class="bg-success">{{ item.name }}</th>
                    </tr>
                    <tr>
                        <template v-for="item in colMonth">
                            <th class="bg-success">W1</th>
                            <th class="bg-success">W2</th>
                            <th class="bg-success">W3</th>
                            <th class="bg-success">W4</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in tableData" :class="getRowClass(item)">
                        <td class="sticky-column">
                            <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', item.location.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.location.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', item.location.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.location.witel_kode) >= 0, 'ms-4': level == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <RouterLink v-else :to="'/ike/'+item.location.id" class="d-flex align-items-center px-4 py-1 tw-group">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                                <VueFeather type="eye" size="1.2rem" class="ms-auto tw-transition-opacity tw-opacity-0 group-hover:tw-opacity-50" />
                            </RouterLink>
                        </td>
                        <td v-for="ikeItem in item.ike" class="middle text-center">
                            {{ ikeItem.isExists ? ikeItem.ike_value : "-" }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>