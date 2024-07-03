<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@/stores/user";
import { usePueV2Store } from "@/stores/pue-v2";
import { getPueBgClass } from "@/helpers/pue-color";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";
import DialogExportLinkVue from "@/components/ui/DialogExportLink.vue";

const emit = defineEmits(["detail"]);
const props = defineProps({
    isLoading: { type: Boolean, default: false }
});

const isLoading = computed(() => props.isLoading);

const pueStore = usePueV2Store();
const userStore = useUserStore();
const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const currDate = new Date();
const currYear = currDate.getFullYear();

const tableData = ref([]);
const hasCollapseInit = ref(false);
const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const isEmpty = val => (val === undefined || val === null);
const getGroupData = (data, groupKey) => {
    const currItem = data[0];
    const avgs = {
        currDay: [],
        currWeek: [],
        currMonth: []
    };

    data.forEach(item => {
        if(item[groupKey] != currItem[groupKey])
            return;
        if(!isEmpty(item?.currDay))
            avgs.currDay.push( toFixedNumber(item.currDay, 2) );
        if(!isEmpty(item?.currWeek))
            avgs.currWeek.push( toFixedNumber(item.currWeek, 2) );
        if(!isEmpty(item?.currMonth))
            avgs.currMonth.push( toFixedNumber(item.currMonth, 2) );
    });

    const groupData = {};
    for(let key in avgs) {
        if(avgs[key].length > 0) {
            let avgItemSum = avgs[key].reduce((result, item) => result += item, 0);
            groupData[key] = toFixedNumber((avgItemSum / avgs[key].length), 2);
        } else {
            groupData[key] = null;
        }
    }

    return groupData;
};

const setData = (srcData) => {
    collapsedDivre.value = [];
    collapsedWitel.value = [];
    const groupKeys = { divre: "", witel: "" };
    const groupedData = srcData.reduce((result, item, index) => {

        if(groupKeys.divre !== item.divre_kode && level.value == "nasional") {
            const divreData = getGroupData(srcData.slice(index), "divre_kode");
            groupKeys.divre = item.divre_kode;
            result.push({
                type: "divre",
                title: item.divre_name,
                divre_kode: item.divre_kode,
                divre_name: item.divre_name,
                ...divreData
            });
            
            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.divre_kode);
        }
        
        if(groupKeys.witel !== item.witel_kode && level.value != "witel") {
            const witelData = getGroupData(srcData.slice(index), "witel_kode");
            groupKeys.witel = item.witel_kode;
            result.push({
                type: "witel",
                title: item.witel_name,
                divre_kode: item.divre_kode,
                divre_name: item.divre_name,
                witel_kode: item.witel_kode,
                witel_name: item.witel_name,
                ...witelData
            });

            const hasWitelCollapsed = collapsedWitel.value.indexOf(item.witel_kode) >= 0;
            if(!hasCollapseInit.value && !hasWitelCollapsed)
                toggleRowCollapse("witel", item.witel_kode);
        }

        item.currDay = !isEmpty(item?.currDay) ? toFixedNumber(item.currDay, 2) : null;
        item.currWeek = !isEmpty(item?.currWeek) ? toFixedNumber(item.currWeek, 2) : null;
        item.currMonth = !isEmpty(item?.currMonth) ? toFixedNumber(item.currMonth, 2) : null;
        result.push({
            type: "sto",
            title: item.rtu_name,
            ...item
        });

        return result;
        
    }, []);
    
    if(!hasCollapseInit.value)
        hasCollapseInit.value = true;
    tableData.value = groupedData;
};
defineExpose({ setData });

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

const showDialogExport = ref(false);
</script>
<template>
    <div class="card border-0">
        <div class="card-header border-top border-start border-end d-flex align-items-center">
            <h5>Tabel nilai PUE tahun {{ currYear }}</h5>
            <button type="button" @click="showDialogExport = true" class="btn-icon ms-auto px-3">
                <VueFeather type="download" size="1em" />
                <span class="ms-2">Export</span>
            </button>
        </div>
        <div v-if="isLoading" class="card-body">
            <div v-for="i in 6" class="row g-4 mb-3">
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
            </div>
        </div>
        <div v-else class="table-responsive bg-white">
            <table class="table table-bordered table-head-primary table-pue">
                <thead>
                    <tr>
                        <th rowspan="2" class="bg-success">Lingkup Kerja</th>
                        <th colspan="3" class="bg-success">Nilai Rata-Rata PUE</th>
                    </tr>
                    <tr>
                        <th>Hari ini</th><th>Minggu ini</th><th>Bulan ini</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in tableData" :class="getRowClass(item)">

                        <td class="sticky-column">
                            <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)"
                                    :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            
                            <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)"
                                    :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0, 'ms-4': level == 'nasional' }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>

                            <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ item.title }}</small>
                            </p>

                            <a v-else role="button" @click="$emit('detail', item.rtu_kode)" class="d-flex align-items-center px-4 py-1 tw-group">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ item.title }}</small>
                                <VueFeather type="eye" size="1.2rem"
                                    class="ms-auto tw-transition-opacity tw-opacity-0 group-hover:tw-opacity-50" />
                            </a>
                        </td>

                        <td :class="!isEmpty(item?.currDay) ? getPueBgClass(item.currDay) : null"
                            class="text-end middle f-w-700">
                            {{ !isEmpty(item?.currDay) ? item.currDay : '-' }}
                        </td>
                        <td :class="!isEmpty(item?.currWeek) ? getPueBgClass(item.currWeek) : null"
                            class="text-end middle f-w-700">
                            {{ !isEmpty(item?.currWeek) ? item.currWeek : '-' }}
                        </td>
                        <td :class="!isEmpty(item?.currMonth) ? getPueBgClass(item.currMonth) : null"
                            class="text-end middle f-w-700">
                            {{ !isEmpty(item?.currMonth) ? item.currMonth : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/pue" title="Export Monitoring PUE Online"
            useDivre useWitel @close="showDialogExport = false" />
    </div>
</template>