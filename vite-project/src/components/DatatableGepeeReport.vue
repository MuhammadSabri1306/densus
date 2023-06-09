<script setup>
import { ref, computed } from "vue";
import { useGepeeReportStore } from "@/stores/gepee-report";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { useCollapseRow } from "@helpers/collapse-row";
import { getPueBgClass } from "@helpers/pue-color";
import { getPercentageTextClass } from "@helpers/percentage-color";
import { toNumberText, toFixedNumber } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

defineEmits(["showCategory"]);

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const gepeeReportStore = useGepeeReportStore();

const tableData = ref([]);
const categoryList = ref([]);
const pueLowLimit = ref(null);
const summaryNasional = ref(null);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = gepeeReportStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});


const arrSum = arr => {
    return arr.reduce((sum, item) => {
        sum += item;
        return sum;
    });
};

const getGroupAvg = (data, groupKey) => {
    let rowCount = 0;
    const perfSum = [];
    const sum = {
        summMonthly: 0,
        summYearly: 0,
        pueOnline: null,
        pueOffline: null
    };
    
    const currItem = JSON.parse(JSON.stringify(data[0]));
    currItem.performance_summary = 0;
    currItem.performance_summary_yearly = 0;

    data.forEach(item => {
        if(item.location[groupKey] != currItem.location[groupKey])
            return;
        
        item.performance.forEach((perf, i) => {
            if(perfSum.length < (i + 1)) {
                perfSum[i] = {
                    count_all: 0,
                    count_approved: 0,
                    percentage: 0,
                    has_schedule: false
                };
            }

            if(perf.has_schedule) {
                perfSum[i].has_schedule = true;
                perfSum[i].count_all += perf.count_all;
                perfSum[i].count_approved += perf.count_approved;
                perfSum[i].percentage += perf.percentage;
            }
        });

        if(item.performance_summary)
            sum.summMonthly += item.performance_summary;
            
        if(item.performance_summary_yearly)
            sum.summYearly += item.performance_summary_yearly;

        if(item.pue.online) {
            if(sum.pueOnline === null)
                sum.pueOnline = 0;
            sum.pueOnline += item.pue.online;
        }

        if(item.pue.offline) {
            if(sum.pueOffline === null)
                sum.pueOffline = 0;
            sum.pueOffline += item.pue.offline;
        }

        rowCount++;
    });

    perfSum.forEach((item, i) => {
        currItem.performance[i].has_schedule = item.has_schedule;
        currItem.performance[i].count_all = item.count_all;
        currItem.performance[i].count_approved = item.count_approved;
        currItem.performance[i].percentage = item.percentage / rowCount;
    });
    
    currItem.performance_summary = sum.summMonthly / rowCount;
    currItem.performance_summary_yearly = sum.summYearly / rowCount;

    currItem.pue.online = sum.pueOnline === null ? null : (sum.pueOnline / rowCount);
    currItem.pue.offline = sum.pueOffline === null ? null : (sum.pueOffline / rowCount);

    let pueValue = null;
    if(currItem.pue.online !== null)
        pueValue = currItem.pue.online;
    if(currItem.pue.offline !== null)
        pueValue = currItem.pue.offline;
    
    if(pueValue == null)
        currItem.pue.isReachTarget = false;
    else if(!pueLowLimit.value)
        currItem.pue.isReachTarget = true;
    else
        currItem.pue.isReachTarget = pueValue > pueLowLimit.value;
    
    return currItem;
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };
    const groupedData = data.reduce((result, item, index) => {

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
        title = item.location.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
    // console.log(groupedData);
    return groupedData;
};

const isLoading = ref(true);
const hasInit = ref(false);
const fetch = () => {
    isLoading.value = true;
    hasInit.value = true;
    gepeeReportStore.getReport(({ data }) => {
        if(data.pueLowLimit)
            pueLowLimit.value = data.pueLowLimit;
        if(data.category)
            categoryList.value = data.category;
        if(data.gepee)
            tableData.value = groupData(data.gepee);

        if(data.gepee_summary_nasional)
            summaryNasional.value = data.gepee_summary_nasional;

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

const isLocationOnline = item => item.type == "sto" && item.location.is_online;
const isPueReachTarget = item => item.type == "sto" && item.pue.isReachTarget;
const formatItemNumber = itemNumb => {
    if(itemNumb === null)
        return "-";
    return toNumberText(itemNumb);
};

const getColClassNumber = (type, itemNumb) => {
    if(type == "pue") {
        if(itemNumb)
            return getPueBgClass(toFixedNumber(itemNumb));
        if(itemNumb === 0)
            return 'f-w-700';
        return "text-muted";
    }

    if(type == "percent")
        return getPercentageTextClass(toFixedNumber(itemNumb));
};

const viewStore = useViewStore();
const monthList = viewStore.monthList;
const selectedMonth = computed(() => {
    const monthNumber = viewStore.filters.month;
    const month = monthList[monthNumber - 1];
    return month ? month?.name : monthNumber;
});

const selectedYear = computed(() => viewStore.filters.year);
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
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-collapsable table-gepee-report">
                <thead>
                    <tr>
                        <th class="bg-success sticky-column" rowspan="2">Lingkup Kerja</th>
                        <th class="bg-success" rowspan="2">ID Pelanggan</th>
                        <th class="bg-success" colspan="2">PUE <small>(Diukur Bulanan)</small></th>
                        <th class="bg-success" rowspan="2">OFFLINE/<br>ONLINE</th>
                        <th class="bg-success" rowspan="2">
                            PUE &lt;= {{ pueLowLimit }}<br><small>YA/TIDAK</small>
                        </th>
                        <th class="bg-success" rowspan="2">Tagihan PLN<br><small>(Tren Bulanan)</small></th>
                        <th class="bg-success" :colspan="categoryList.length+1">
                            Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)
                        </th>
                        <th rowspan="2">% Gepee Activity<br><small>(Bulan {{ selectedMonth }})</small></th>
                        <th rowspan="2">% Gepee Activity<br><small>(Tahun {{ selectedYear }})</small></th>
                    </tr>
                    <tr>
                        <th>OFFLINE</th>
                        <th>ONLINE</th>
                        <th v-for="category in categoryList" @click="$emit('showCategory')"
                            class="tw-cursor-pointer btn-primary category-tooltip">
                            <p class="text-center mb-0">{{ category.alias }}</p>
                            <span class="badge badge-light text-dark border-primary">{{ category.activity }}</span>
                        </th>
                        <th>Replacement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="summaryNasional">
                        <td class="sticky-column !tw-bg-[#24695c] text-white">
                            <b class="px-3">SUMMARY NASIONAL</b>
                        </td>
                        <td class="middle text-center">-</td>
                        <td :class="getColClassNumber('pue', summaryNasional.pue_offline)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.pue_offline) }}
                        </td>
                        <td :class="getColClassNumber('pue', summaryNasional.pue_online)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.pue_online) }}
                        </td>
                        <td></td>
                        <td class="middle text-center">
                            {{ summaryNasional.isPueReachTarget ? "TIDAK" : "YA" }}
                        </td>
                        <td class="middle text-center">-</td>
                        <td v-for="percentage in summaryNasional.performance"
                            :class="getColClassNumber('percent', percentage)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(percentage) }}%
                        </td>
                        <td class="middle text-center">-</td>
                        <td :class="getColClassNumber('percent', summaryNasional.performance_summary)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.performance_summary) }}%
                        </td>
                        <td :class="getColClassNumber('percent', summaryNasional.performance_summary_yearly)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.performance_summary_yearly) }}%
                        </td>
                    </tr>
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

                            <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ item.title }}</small>
                            </p>
                            
                        </td>
                        <td class="middle text-center">{{ (item.type == 'sto') ? item.location.id_pel_pln : '-' }}</td>
                        <td :class="getColClassNumber('pue', item.pue.offline)" class="middle text-center f-w-700">
                            {{ formatItemNumber(item.pue.offline) }}
                        </td>
                        <td :class="getColClassNumber('pue', item.pue.online)" class="middle text-center f-w-700">
                            {{ formatItemNumber(item.pue.online) }}
                        </td>
                        <td class="middle text-center">
                            {{ (item.type != 'sto') ? '' : isLocationOnline(item) ? "ONLINE" : "OFFLINE" }}
                        </td>
                        <td class="middle text-center">{{ isPueReachTarget(item) ? "TIDAK" : "YA" }}</td>
                        <td class="middle text-center">-</td>
                        <td v-for="category in item.performance" :class="getColClassNumber('percent', category.percentage)"
                            class="middle text-center f-w-700">
                            <span v-if="!category.has_schedule">-</span>
                            <span v-else>{{ formatItemNumber(category.percentage) }}%</span>
                        </td>
                        <td class="middle text-center f-w-700">-</td>
                        <td :class="getColClassNumber('percent', item.performance_summary)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(item.performance_summary) }}%
                        </td>
                        <td :class="getColClassNumber('percent', item.performance_summary_yearly)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(item.performance_summary_yearly) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<style scoped>

.table-gepee-report th {
    text-align: center;
    white-space: nowrap;
}

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