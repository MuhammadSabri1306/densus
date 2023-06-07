<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { groupByLocation } from "@helpers/location-group";

const emit = defineEmits(["update", "showCategory"]);
const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const activityStore = useActivityStore();
const userStore = useUserStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = activityStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const monthList = computed(() => {
    const filterMonth = activityStore.filters.month;
    const list = activityStore.monthList;
    const category = activityStore.category;

    return list
        .filter(monthItem => !filterMonth || monthItem.number === filterMonth)
        .map(monthItem => ({ ...monthItem, category }));
});

const tableData = ref([]);
const isAdmin = computed(() => userStore.role == "admin");

const setupData = () => {
    const schedule = activityStore.schedule;
    const location = activityStore.location;
    
    const dataSetup = location.map(locItem => {
        const col = monthList.value.reduce((monthCol, monthItem) => {
            let category = monthItem.category;
            for(let i=0; i<category.length; i++) {

                const categoryItem = { number: i+1, ...category[i] };
                const scheduleCol = schedule.find(scheItem => {
                    const isLocationMatch = scheItem.id_lokasi == locItem.id;
                    const isMonthMatch = (scheItem.createdAt.getMonth() + 1) == monthItem.number;
                    const isCategoryMatch = scheItem.id_category == categoryItem.id;
                    return isLocationMatch && isMonthMatch && isCategoryMatch;
                });
                
                const dataItem = { isExists: false };
                if(scheduleCol && scheduleCol.value == 1) {
                    dataItem.isExists = true;
                    dataItem.isChecked = scheduleCol.execution_count > 0;
                    dataItem.scheduleId = scheduleCol.id;
                    dataItem.isDisabled = !isAdmin.value && scheduleCol.updatable?.execution == 0;
                    dataItem.label = `${ categoryItem.activity } bulan ${ monthItem.number }`;
                    dataItem.execCount = scheduleCol.execution_count || 0;
                    dataItem.apprCount = scheduleCol.approved_count || 0;
                }

                monthCol.push(dataItem);
            }
            return monthCol;
        }, []);
        return { ...locItem, col };
    });

    tableData.value = groupByLocation({
        data: dataSetup,
        divre: {
            groupKey: "divre_kode",
            checker: isMatch => isMatch && level.value == "nasional",
            formatTitle: item => item.divre_name
        },
        witel: {
            groupKey: "witel_kode",
            checker: isMatch => isMatch && level.value != "witel",
            formatTitle: item => "WILAYAH " + item.witel_name
        },
        sto: { formatTitle: item => item.sto_name }
    });
};

await activityStore.fetchSchedule(true, () => setupData());

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

const getColTdClass = item => {
    if(!item.isExists)
        return;

    const { execCount, apprCount } = item;

    if(execCount < 1)
        return "activity-bg-danger";
    if(apprCount < execCount)
        return "activity-bg-warning";
    return "activity-bg-success";
};
</script>
<template>
    <div class="table-responsive table-freeze bg-white pb-3">
        <table class="table table-bordered table-head-primary table-gepee">
            <thead>
                <tr>
                    <th rowspan="3" class="bg-success sticky-column">Lingkup Kerja</th>
                    <th v-for="item in monthList" :colspan="item.category.length" class="bg-success">{{ item.name }}</th>
                </tr>
                <tr>
                    <th v-for="item in monthList" :colspan="item.category.length">Activity Categories</th>
                </tr>
                <tr>
                    <template v-for="mItem in monthList">
                        <th v-for="cItem in mItem.category" @click="$emit('showCategory')" class="tw-cursor-pointer btn-primary category-tooltip">
                            <p class="text-center mb-0">{{ cItem.alias }}</p>
                            <span class="badge badge-light text-dark border-primary">{{ cItem.activity }}</span>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in tableData" :class="getRowClass(item)">
                    <td class="sticky-column">
                        <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                            <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0, 'ms-4': level == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                <VueFeather type="chevron-right" size="1rem" />
                            </button>
                            <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                        </div>
                        <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                            <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                        </p>
                    </td>
                    <!-- <CheckColumn v-else-if="item.type == 'sto'" :key="item.id" :rowData="item.col" /> -->
                    <template v-if="item.type == 'sto'">
                        <td v-for="col in item.col" :class="getColTdClass(col)" class="text-center middle">
                            <input type="checkbox" v-if="col.isExists" :checked="col.isChecked" @click.prevent="$router.push('/gepee/exec/' + col.scheduleId)"
                                :disabled="col.isDisabled" :aria-label="col.label" class="form-check-input" />
                        </td>
                    </template>
                    <template v-else>
                        <td colspan="96" class="bg-light"></td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<style scoped>

.category-tooltip {
    @apply tw-relative;
}

.category-tooltip .badge {
    @apply tw-absolute tw-right-0 -tw-top-8 tw-z-[9] tw-transition-opacity tw-opacity-0 tw-pointer-events-none;
}

.category-tooltip:hover .badge {
    @apply tw-opacity-100;
}

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