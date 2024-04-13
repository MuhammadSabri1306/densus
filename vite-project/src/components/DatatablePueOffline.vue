<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { useUserStore } from "@/stores/user";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";
import DialogPueOfflineLocation from "@/components/DialogPueOfflineLocation.vue";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueStore = usePueV2Store();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueStore.filters;
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

        for(let i=0; i<currItem.pue.length; i++) {
            if(sum.length <= i) {
                sum.push(0);
                isExists.push(false);
            }
            sum[i] += Number(dataItem.pue[i].pue_value);
            if(dataItem.pue[i].isExists)
                isExists[i] = true;
        }
        count++;
    });
    
    for(let i=0; i<currItem.pue.length; i++) {
        const pueValue = count < 1 ? 0 : sum[i] / count;
        currItem.pue[i].pue_value = toFixedNumber(pueValue, 2);
        currItem.pue[i].isExists = isExists[i];
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
        title = item.location.lokasi;
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
    const monthList = pueStore.monthList;
    const selectedMonth = pueStore.filters.month;

    if(fetchedMonth.length > 0)
        return fetchedMonth;
    if(!selectedMonth)
        return monthList;
    return [ monthList[selectedMonth - 1] ];
});

const tableData = ref([]);
const setupData = rawDataLocation => {
    const monthList = pueStore.monthList;
    const selectedMonth = pueStore.filters.month;
    
    const month = !selectedMonth ? monthList : [ monthList[selectedMonth - 1] ];
    const data = [];

    rawDataLocation.forEach((item, rowIndex) => {

        data.push({ location: item.location, pue: [] });
        const pueOnLocation = item.pue;
        month.forEach(monthItem => {
            for(let weekNumber=1; weekNumber<=4; weekNumber++) {

                const pueIndex = pueOnLocation.findIndex(pueItem => {
                    if(!pueItem)
                        return false;
                    const isMonthMatched = Number(pueItem.month) === monthItem.number;
                    const isWeekMatched = Number(pueItem.week) === weekNumber;
                    return isMonthMatched && isWeekMatched;
                });
                
                const pueCol = {
                    month: monthItem.number,
                    week: weekNumber,
                };

                if(pueIndex < 0) {
                    pueCol.isExists = false;
                    pueCol.pue_value = 0;
                } else {
                    pueCol.isExists = true;
                    pueCol.pue_value = toFixedNumber(pueOnLocation[pueIndex].pue_value, 2);
                    pueOnLocation.splice(pueIndex, 1);
                }
                data[rowIndex].pue.push(pueCol);

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
    pueStore.getOfflineLocationData(({ data }) => {
        if(data.location_data) {
            resetCollapse();
            setupData(data.location_data);
        }
        isLoading.value = false;
    });
};

defineExpose({ fetch });
const showFormAddLocation = ref(false);

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
                            <RouterLink v-else :to="'/pue/offline/'+item.location.id_location" class="d-flex align-items-center px-4 py-1 tw-group">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                                <VueFeather type="eye" size="1.2rem" class="ms-auto tw-transition-opacity tw-opacity-0 group-hover:tw-opacity-50" />
                            </RouterLink>
                        </td>
                        <td v-for="pueItem in item.pue" class="middle text-center">
                            {{ pueItem.isExists ? pueItem.pue_value : "-" }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <DialogPueOfflineLocation v-if="showFormAddLocation" @close="showFormAddLocation = false" @save="fetch" />
    </div>
</template>