<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
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
};

const getPercentage = (approved, total) => {
    approved = Number(approved);
    total = Number(total);

    if(total > 0)
        return approved / total * 100;
    return 0;
};

const getSumCollumn = (currCol, data) => {
    const { type, title, location } = currCol;
    const key = type + "_kode";
    const code = location[key];
    
    let totalSum = 0;
    let approvedSum = 0;
    const col = [];
    
    data.forEach((item, index) => {
        if(index === 0) {
            for(let i of item.col) {
                const schedule = { approved_count: 0, execution_count: 0 };
                col.push({ schedule });
            }
        }

        if(item.type != "sto" || item.location[key] !== code)
            return;
        item.col.forEach((colItem, colIndex) => {
            
            if(!colItem.schedule)
                return;
            col[colIndex].consistency = getPercentage(colItem.schedule.approved_count, colItem.schedule.execution_count);
            
        });
    });
    
    const consistency = (totalSum > 0) ? approvedSum / totalSum * 100 : 100;
    return { type, title, location, col, consistency };
};

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);

const tableData = computed(() => {
    const data = activityStore.scheduleTable;
    let divreCode = "";
    let witelCode = "";

    const result = data.reduce((res, item) => {
        
        let type = null;
        let title = null;

        if(divreCode !== item.location.divre_kode && userLevel.value == "nasional") {
            type = "divre";
            title = item.location.divre_name;
            
            res.push({ type, title, ...item });
            divreCode = item.location.divre_kode;
        }
        
        if(witelCode !== item.location.witel_kode && userLevel.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            
            res.push({ type, title, ...item });
            witelCode = item.location.witel_kode;
        }
        
        type = "sto";
        title = item.location.sto_name;
        
        const col = item.col.map(colItem => {
            if(!colItem.schedule)
                return colItem;
            const consistency = getPercentage(colItem.schedule.approved_count, colItem.schedule.execution_count);
            return { consistency, ...colItem };
        });
        item.col = col;
        
        res.push({ type, title, ...item });
        return res;
        
    }, []);

    return result.map(item => {
        if(item.type == "sto")
            return item;
        return getSumCollumn(item, result);
    })
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
                    <!-- <th rowspan="3" class="bg-success">% Konsistensi</th> -->
                    <th v-for="item in monthList" :colspan="categoryList.length" class="bg-success">{{ item.name }}</th>
                </tr>
                <tr>
                    <th v-for="item in monthList" :colspan="categoryList.length">Konsistensi Activity (%)</th>
                </tr>
                <tr>
                    <template v-for="month in monthList">
                        <th v-for="item in categoryList" @click="showCategory = true" class="tw-cursor-pointer btn-primary">
                            <p class="text-center mb-0">{{ item.alias }}</p>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in tableData" :class="{ 'row-collapsed': ((item.type == 'sto' && collapsedWitel.indexOf(item.location.witel_kode) >= 0) || (item.type == 'sto' && collapsedDivre.indexOf(item.location.divre_kode) >= 0)) || (item.type == 'witel' && collapsedDivre.indexOf(item.location.divre_kode) >= 0) }">
                    <td class="sticky-column">
                        <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('divre', item.location.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.location.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('witel', item.location.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.location.witel_kode) >= 0, 'ms-4': userLevel == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        <div v-else class="px-4 py-1 tw-whitespace-nowrap"><small :class="{ 'ps-5': userLevel != 'witel', 'ms-5': userLevel == 'nasional' }" class="fw-semibold">{{ item.title }}</small></div>
                    </td>
                    <!-- <td class="f-w-700 text-end middle">{{ toFixedNumber(item.consistency, 2) }}%</td> -->
                    <template v-if="isCategoryLoading || isScheduleLoading">
                        <td colspan="96">
                            <Skeleton class="mt-3" />
                        </td>
                    </template>
                    <CheckColumn v-else :rowData="item.col"  />
                </tr>
            </tbody>
        </table>
        <DialogActivityCategory v-model:visible="showCategory" />
    </div>
</template>