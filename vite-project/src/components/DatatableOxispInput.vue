<script setup>
import { ref, computed } from "vue";
import { useOxispStore } from "@/stores/oxisp";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { useCollapseRow } from "@/helpers/collapse-row";
import Skeleton from "primevue/skeleton";
import { useRouter } from "vue-router";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const oxispStore = useOxispStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = oxispStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const tableData = ref([]);
const monthList = ref([]);

const viewStore = useViewStore();
const monthColName = computed(() => {
    return monthList.value.map(number => {
        const month = viewStore.monthList[number - 1];
        return month ? month.name : null;
    });
});

const getGroupAvg = (data, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[0]));
    const sum = [];

    data.forEach(item => {
        if(item.location[groupKey] != currItem.location[groupKey])
            return;
        
        for(let i=0; i<item.location_data.length; i++) {
            if(sum.length <= i) {
                sum.push({
                    allCount: 0,
                    approvedCount: 0
                });
            }

            sum[i].allCount += item.location_data[i].all_count;
            sum[i].approvedCount += item.location_data[i].approved_count;
        }
    });

    for(let i=0; i<sum.length; i++) {
        currItem.location_data[i].all_count = sum[i].allCount;
        currItem.location_data[i].approved_count = sum[i].approvedCount;
        currItem.location_data[i].is_exist = false;
    }
    
    return currItem;
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };
    return data.reduce((result, item, index) => {

        let type = null;
        let title = null;

        if(groupKeys.divre !== item.location.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.location.divre_name;
            const divreData = getGroupAvg(data.slice(index), "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.location.divre_kode;
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            const witelData = getGroupAvg(data.slice(index), "witel_kode");

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.location.witel_kode;
        }
        
        type = "sto";
        title = item.location.sto_kode + " / " + item.location.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const hasInit = ref(false);
const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    oxispStore.getPerformance(({ success, data }) => {
        if(data.performance)
            tableData.value = groupData(data.performance);
        if(data.month_list)
            monthList.value = data.month_list;
        hasInit.value = success;
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

const getTdClass = item => {
    const { all_count, approved_count } = item;
    if(all_count < 1)
        return "activity-bg-danger";
    if(approved_count < all_count)
        return "activity-bg-warning";
    return "activity-bg-success";
};

const getCheckLabel = (year, month) => `Activity bulan ${ month } tahun ${ year }`;

const router = useRouter();
const onCheckClick = (year, month, idLocation) => {
    router.push(`/oxisp/activity/${ year }/${ month }/${ idLocation }`);
};
</script>
<template>
    <div>
        <div v-if="isLoading" class="card card-body">
            <div class="row gy-4">
                <div v-for="item in 24" class="col-3 col-lg-4">
                    <Skeleton />
                </div>
            </div>
        </div>
        <div v-else-if="!hasInit" class="px-4 py-3 border">
            <h4 class="text-center">Gagal memuat data.</h4>
        </div>
        <div v-else-if="tableData.length < 1" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-collapsable table-gepee-report">
                <thead>
                    <tr>
                        <th rowspan="2" class="bg-success sticky-column">Lingkup Kerja</th>
                        <th :colspan="monthColName.length" class="bg-success">Activity <small>Per Bulan</small></th>
                    </tr>
                    <tr>
                        <th v-for="monthName in monthColName" class="bg-success">{{ monthName }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in tableData" :class="getRowClass(row)">
                        <td class="sticky-column">
                            <div v-if="row.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', row.location.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(row.location.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
                            <div v-else-if="row.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', row.location.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(row.location.witel_kode) >= 0, 'ms-4': level == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
                            <RouterLink v-else :to="'/oxisp/activity/'+row.location.id_sto" :title="row.title" class="d-flex align-items-center px-4 py-1 tw-group">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ row.title }}</small>
                                <VueFeather type="eye" size="1.2rem" class="ms-auto tw-transition-opacity tw-opacity-0 group-hover:tw-opacity-50" />
                            </RouterLink>
                        </td>
                        <template v-if="row.type == 'sto'">
                            <td v-for="col in row.location_data" :class="getTdClass(col)" class="text-center middle">
                                <input type="checkbox" :checked="col.approved_count" @click.prevent="onCheckClick(col.year, col.month, row.location.id_sto)"
                                    :disabled="!col.is_updatable" :aria-label="getCheckLabel(col.year, col.month)"
                                    class="form-check-input form-check-input-lg" />
                            </td>
                        </template>
                        <td v-else :colspan="monthColName.length" class="bg-light"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<style scoped>

td.activity-bg-success {
    background-color: #2cb198!important;
}

td.activity-bg-warning {
    background-color: #ffe55c!important;
}

td.activity-bg-danger {
    background-color: #ff658d!important;
}

</style>