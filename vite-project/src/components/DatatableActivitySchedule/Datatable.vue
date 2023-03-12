<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import Skeleton from "primevue/skeleton";
import CheckColumn from "./CheckColumn.vue";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";

const activityStore = useActivityStore();
const collapsedDivre = ref([]);
const collapsedWitel = ref([]);

const monthList = computed(() => {
    const filterMonth = activityStore.filters.month;
    const list = activityStore.monthList;

    if(!filterMonth)
        return list;
    return [list.find(item => item.number === filterMonth)];
});

const showCategory = ref(false);

const collapseRow = (type, code) => {
    if(type == "divre")
        collapsedDivre.value = [...collapsedDivre.value, code];
    if(type == "witel")
        collapsedWitel.value = [...collapsedWitel.value, code];
};

const expandRow = (type, code) => {
    if(type == "divre")
        collapsedDivre.value = collapsedDivre.value.filter(item => item !== code);
    if(type == "witel")
        collapsedWitel.value = collapsedWitel.value.filter(item => item !== code);
};

const toggleRowCollapse = (type, code) => {
    const isExpand = (type == "divre") ? collapsedDivre.value.indexOf(code) < 0 : collapsedWitel.value.indexOf(code) < 0;
    if(isExpand)
        collapseRow(type, code);
    else
        expandRow(type, code);
    console.log(collapsedWitel.value)
};

const tableData = computed(() => {
    const data = activityStore.scheduleTable;
    let witelCode = "";

    return data.reduce((res, item) => {
        
        let type = null;
        let title = null;
        
        if(witelCode !== item.location.witel_kode) {
            type = "witel";
            title = "Witel " + item.location.witel_name;
            res.push({ type, title, ...item });
            witelCode = item.location.witel_kode;
        }
        
        type = "sto";
        title = item.location.sto_name;
        
        res.push({ type, title, ...item });
        return res;
        
    }, []);
});

const isCategoryLoading = ref(true);
const isScheduleLoading = ref(true);
activityStore.fetchCategory(false, () => {
    isCategoryLoading.value = false;
    activityStore.fetchSchedule(true, () => isScheduleLoading.value = false);
});

const categoryList = computed(() => activityStore.category);
</script>
<template>
    <div class="table-responsive table-freeze bg-white pb-3">
        <table class="table table-bordered table-head-primary table-gepee">
            <thead>
                <tr>
                    <th rowspan="3" class="bg-success sticky-column">Lingkup Kerja</th>
                    <th v-for="item in monthList" :colspan="categoryList.length" class="bg-success">{{ item.name }}</th>
                </tr>
                <tr>
                    <th v-for="item in monthList" :colspan="categoryList.length">Activity Categories</th>
                </tr>
                <tr>
                    <template v-if="tableData.length > 0 && tableData[0].col">
                        <th v-for="item in tableData[0].col" @click="showCategory = true" class="tw-cursor-pointer btn-primary">
                            <p class="text-center mb-0">{{ item.category?.alias }}</p>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in tableData" :class="{ 'row-collapsed': (item.type == 'sto' && collapsedWitel.indexOf(item.location.witel_kode) >= 0) || (item.type == 'witel' && collapsedDivre.indexOf(item.divre_kode) >= 0) }">
                    <td class="sticky-column">
                        <div v-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('witel', item.location.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.location.witel_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        <div v-else class="px-4 py-1 tw-whitespace-nowrap"><small class="ps-5 fw-semibold">{{ item.title }}</small></div>
                    </td>
                    <template v-if="isCategoryLoading || isScheduleLoading">
                        <td colspan="96">
                            <Skeleton class="mt-3" />
                        </td>
                    </template>
                    <CheckColumn v-else-if="item.type == 'sto'" :rowData="item.col"  />
                    <template v-else>
                        <td colspan="96" class="bg-light"></td>
                    </template>
                </tr>
            </tbody>
        </table>
        <DialogActivityCategory v-model:visible="showCategory" />
    </div>
</template>