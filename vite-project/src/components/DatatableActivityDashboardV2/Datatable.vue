<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { toFixedNumber } from "@helpers/number-format";
import { getPercentageTextClass } from "@helpers/percentage-color";

defineEmits(["showCategory"]);
const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const activityStore = useActivityStore();
const userStore = useUserStore();
const level = computed(() => {
    const userLevel = userStore.level;
    const filters = activityStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, index, groupKey) => {
    let count = 0;
    const sum = [];
    const currItem = JSON.parse(JSON.stringify(data[index]));

    data.forEach(dataItem => {
        if(dataItem.location[groupKey] != currItem.location[groupKey])
            return;

        for(let i=0; i<currItem.item.length; i++) {
            if(sum.length <= i)
                sum[i] = 0;
            sum[i] += Number(dataItem.item[i].percent);
        }

        count++;
    });

    for(let i=0; i<currItem.item.length; i++) {
        currItem.item[i].percent = count < 1 ? 0 : sum[i] / count;
        currItem.item[i].isExists = true;

        delete currItem.item[i].id_schedule;
        delete currItem.item[i].exec_count;
        delete currItem.item[i].approved_count;
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
            const divreData = getGroupAvg(data, index, "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.location.divre_kode;
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            const witelData = getGroupAvg(data, index, "witel_kode");
            
            result.push({ type, title, ...witelData });
            groupKeys.witel = item.location.witel_kode;
        }
        
        type = "sto";
        title = item.location.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const tableData = ref([]);
const monthCol = ref([]);
const categories = ref([]);
const hasSetup = ref(false);

const setData = data => {
    if(data.performance)
        tableData.value = groupData(data.performance);
    if(data.monthCol)
        monthCol.value = data.monthCol;
    if(data.categories)
        categories.value = data.categories;
    hasSetup.value = true;
};

defineExpose({ setData });

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

const getRoute = idSchedule => "/gepee/" + idSchedule;
</script>
<template>
    <div v-if="hasSetup" class="pb-3">
        <div v-if="tableData.length < 1" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-gepee">
                <thead>
                    <tr>
                        <th rowspan="3" class="bg-success sticky-column">Lingkup Kerja</th>
                        <th v-for="monthName in monthCol" :colspan="categories.length" class="bg-success">{{ monthName }}</th>
                    </tr>
                    <tr>
                        <th v-for="item in monthCol" :colspan="categories.length">Konsistensi Activity (%)</th>
                    </tr>
                    <tr>
                        <template v-for="m in monthCol">
                            <th v-for="category in categories" @click="$emit('showCategory')"
                                class="tw-cursor-pointer btn-primary category-tooltip">
                                <p class="text-center mb-0">{{ category.alias }}</p>
                                <span class="badge badge-light text-dark border-primary">{{ category.activity }}</span>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in tableData" :class="getRowClass(row)">
                        <td class="sticky-column">
    
                            <div v-if="row.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', row.location.divre_kode)"
                                    :class="{ 'child-collapsed': collapsedDivre.indexOf(row.location.divre_kode) >= 0 }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
    
                            <div v-else-if="row.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', row.location.witel_kode)"
                                    :class="{ 'child-collapsed': collapsedWitel.indexOf(row.location.witel_kode) >= 0, 'ms-4': level == 'nasional' }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
    
                            <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ row.title }}</small>
                            </p>
    
                        </td>
                        <td v-for="colItem in row.item" :class="{ 'hover:tw-bg-light position-relative': colItem.id_schedule }"
                            class="text-center middle">
    
                            <span v-if="row.type != 'sto'" :class="getPercentageTextClass(toFixedNumber(colItem.percent, 2))"
                                class="tw-cursor-default f-w-700">
                                {{ toFixedNumber(colItem.percent, 2) }}%
                            </span> 
    
                            <RouterLink v-else-if="colItem.isExists" :to="getRoute(colItem.id_schedule)"
                                :class="getPercentageTextClass(toFixedNumber(colItem.percent, 2))"
                                class="stretched-link f-w-700">
                                {{ toFixedNumber(colItem.percent, 2) }}%
                            </RouterLink>
    
                            <span v-else class="tw-cursor-default f-w-700 text-muted">-</span>
    
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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

</style>