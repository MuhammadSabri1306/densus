<script setup>
import { ref, computed } from "vue";
import { useKwhStore } from "@/stores/kwh";
import { useUserStore } from "@/stores/user";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toNumberText } from "@/helpers/number-format";

const kwhStore = useKwhStore();
const userStore = useUserStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = kwhStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const titleMonthYear = computed(() => {
    const filter = kwhStore.filters;
    const selectedMonth = kwhStore.monthList[filter.month - 1];
    return `${ selectedMonth?.name } ${ filter.year }`;
});

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const hasCollapseInit = ref(false);
const resetCollapse = () => {
    hasCollapseInit.value = false;
    collapsedDivre.value = [];
    collapsedWitel.value = [];
};

const getGroupAvg = (data, groupKey) => {
    const kwhSum = {};
    const currItem = JSON.parse(JSON.stringify(data[0]));
    const rtus = currItem.rtus;

    data.forEach(item => {
        if(item[groupKey] != currItem[groupKey])
            return;
        
        rtus.push(...item.rtus);
        for(const key in item.kwh_values) {
            if(kwhSum[key] === undefined)
                kwhSum[key] = null;

            if(item.kwh_values[key] !== null) {
                if(kwhSum[key] === null)
                    kwhSum[key] = item.kwh_values[key].value;
                else
                    kwhSum[key] += item.kwh_values[key].value;
            }
        }

    });

    currItem.rtus = rtus;
    currItem.kwh_values = kwhSum;

    return currItem;
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };
    const groupedData = data.reduce((result, item, index) => {

        let type = null;
        let title = null;

        if(groupKeys.divre !== item.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.divre_name;
            const divreData = getGroupAvg(data.slice(index), "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.divre_kode;
            
            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.divre_kode);
        }
        
        if(groupKeys.witel !== item.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.witel_name;
            const witelData = getGroupAvg(data.slice(index), "witel_kode");

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.witel_kode;

            const hasWitelCollapsed = collapsedWitel.value.indexOf(item.witel_kode) >= 0;
            if(!hasCollapseInit.value && !hasWitelCollapsed)
                toggleRowCollapse("witel", item.witel_kode);
        }
        
        type = "sto";
        title = item.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
    
    if(!hasCollapseInit.value)
        hasCollapseInit.value = true;
    return groupedData;
};

const kwhData = ref([]);
const dayColumn = ref([]);

const setSrcData = data => {
    resetCollapse();
    if(data.day_column)
        dayColumn.value = data.day_column;
    if(data.kwh_data)
        kwhData.value = groupData(data.kwh_data);
};

defineExpose({ setSrcData });

const getRowClass = item => {
    let collapsed = false;
    if(item.type == "sto") {
        const isWitelCollapsed = collapsedWitel.value.indexOf(item.witel_kode) >= 0;
        const isDivreCollapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
        collapsed = isWitelCollapsed || isDivreCollapsed;
    } else if(item.type == "witel") {
        collapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
    }
    
    return { "row-collapsed": collapsed };
};

const getBtnToggleClasses = item => {
    if(item.type == "divre")
        return { "child-collapsed": collapsedDivre.value.indexOf(item.divre_kode) >= 0 };
    if(item.type == "witel")
        return { "child-collapsed": collapsedWitel.value.indexOf(item.witel_kode) >= 0, "ms-4": level.value == "nasional" };
};
</script>
<template>
    <div v-if="kwhData.length < 1" class="px-4 py-3 border">
        <h4 class="text-center">Belum ada data.</h4>
    </div>
    <div v-else class="table-responsive table-freeze bg-white pb-3">
        <table class="table table-bordered table-head-primary table-collapsable">
            <thead>
                <tr>
                    <th class="bg-success sticky-column" rowspan="3">Lingkup Kerja</th>
                    <th class="bg-success" :colspan="dayColumn.length">{{ titleMonthYear }}</th>
                </tr>
                <tr>
                    <th class="bg-success" :colspan="dayColumn.length">Monitoring Nilai KwH</th>
                </tr>
                <tr>
                    <th v-for="day in dayColumn" class="text-center">{{ day }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in kwhData" :class="getRowClass(item)">
                    <td class="sticky-column">

                        <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)" :class="getBtnToggleClasses(item)"
                                class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        
                        <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)" :class="getBtnToggleClasses(item)"
                                class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>

                        <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                            <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                class="fw-semibold">STO {{ item.title }}</small>
                        </p>
                        
                    </td>
                    <template v-for="kwh in item.kwh_values">
                        <td v-if="!kwh" class="f-w-700 text-center align-middle">-</td>
                        <td v-else-if="item.type != 'sto'" class="text-end tw-whitespace-nowrap align-middle">
                            {{ toNumberText(kwh) }} KwH
                        </td>
                        <td v-else class="text-end tw-whitespace-nowrap align-middle">
                            {{ toNumberText(kwh.value) }} KwH
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</template>