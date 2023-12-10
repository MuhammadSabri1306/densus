<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { useCollapseRow } from "@helpers/collapse-row";
import { toFixedNumber, toNumberText, toRoman } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

const userStore = useUserStore();
const pueStore = usePueV2Store();
const viewStore = useViewStore();
const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const tableData = ref([]);
const tableDataYear = ref(pueStore.filters.year);
const tableDataMonth = ref(pueStore.filters.month);
const weeks = ref([]);

const monthTitle = computed(() => {
    const year = tableDataYear.value;
    const monthNumber = tableDataMonth.value;
    if(!year || !monthNumber)
        return "Rata-rata PUE Mingguan";

    const monthName = viewStore.monthList[monthNumber - 1].name;
    return `Rata-rata PUE ${ monthName } ${ year }`;
});

const weekColTitles = computed(() => {
    const weekList = weeks.value;
    return weekList.map((week, index) => {
        const weekNumber = week?.number_of_month || (index + 1);
        return `Minggu ${ toRoman(weekNumber) }`;
    });
});

const getGroupAvg = (data, index, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[index]));
    const pueValues = [];

    data.forEach(item => {
        if(item.location[groupKey] != currItem.location[groupKey])
            return;
        
        for(let key in item.pue) {
            if(!pueValues[key])
                pueValues[key] = [];
            if(item.pue[key].pue_value !== null)
                pueValues[key].push(item.pue[key].pue_value);
        }
    });

    Object.entries(pueValues).forEach(([key, values]) => {
        currItem.pue[key].pue_value = null;
        if(values.length > 0) {
            const valueSum = values.reduce((sum, val) => sum += val, 0);
            const value = valueSum / values.length;
            currItem.pue[key].pue_value = toFixedNumber(value);
        }
    });

    delete currItem.entries;

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

        if(item?.pue) {
            Object.entries(([key, pueItem]) => {
                if(pueItem?.pue_value)
                    item.pue[key] = toFixedNumber(pueItem.pue_value);
            });
        }

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

const isLoading = ref(true);
const hasInit = ref(false);
const fetch = () => {
    isLoading.value = true;
    hasInit.value = true;
    pueStore.getOfflineLocationDataV2(({ data }) => {

        tableData.value = data?.location_data ? groupData(data.location_data) : [];
        if(data.month) tableDataMonth.value = data.month;
        if(data.year) tableDataYear.value = data.year;
        weeks.value = data?.weeks || [];

        resetCollapse();
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
                        <th :colspan="weekColTitles.length" class="bg-success">{{ monthTitle }}</th>
                    </tr>
                    <tr>
                        <th v-for="weekTitle in weekColTitles" class="bg-success tw-whitespace-nowrap">
                            {{ weekTitle }}
                        </th>
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
                            <RouterLink v-else :to="'/pue/offline/'+item.location.id" class="d-flex align-items-center px-4 py-1 tw-group">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                                <VueFeather type="eye" size="1.2rem" class="ms-auto tw-transition-opacity tw-opacity-0 group-hover:tw-opacity-50" />
                            </RouterLink>
                        </td>
                        <template v-for="pueItem in item.pue">
                            <td v-if="pueItem.pue_value !== null" class="middle text-center">
                                {{ toNumberText(pueItem.pue_value) }}
                            </td>
                            <td v-else class="middle text-center f-w-700">-</td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>