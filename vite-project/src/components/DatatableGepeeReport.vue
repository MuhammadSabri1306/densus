<script setup>
import { ref, computed } from "vue";
import { useGepeeReportStore } from "@/stores/gepee-report";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { useCollapseRow } from "@/helpers/collapse-row";
import { getPueBgClass } from "@/helpers/pue-color";
import { getPercentageTextClass } from "@/helpers/percentage-color";
import { toNumberText, toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

defineEmits(["showCategory"]);

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const gepeeReportStore = useGepeeReportStore();

const tableData = ref([]);
const categoryList = ref([]);
const pueMaxTarget = ref(null);
const summaryNasional = ref(null);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = gepeeReportStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, groupKey) => {
    let rowCountPerf = [];
    let rowCountSummMonthly = 0;
    let rowCountSummYearly = 0;
    let rowCountPueOnline = 0;
    let rowCountIke = 0;
    let rowCountPlnSaving = 0;
    let rowCountPlnSavingPercent = 0;
    let rowCountPlnSavingYoy = 0;
    let rowCountPlnSavingYoyPercent = 0;

    const perfSum = [];
    const sum = {
        summMonthly: 0,
        summYearly: 0,
        plnBill: null,
        plnSaving: null,
        plnSavingPercent: null,
        plnSavingYoy: null,
        plnSavingYoyPercent: null,
        pueOnline: null,
        ike: null
    };
    
    const currItem = JSON.parse(JSON.stringify(data[0]));
    currItem.performance_summary = 0;
    currItem.performance_summary_yearly = 0;
    currItem.tagihan_pln = null;
    currItem.pln_saving = null;
    currItem.pln_saving_percent = null;
    currItem.pln_saving_yoy = null;
    currItem.pln_saving_yoy_percent = null;

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
                rowCountPerf[i] = 0;
            }

            if(perf.has_schedule) {
                perfSum[i].has_schedule = true;
                perfSum[i].count_all += perf.count_all;
                perfSum[i].count_approved += perf.count_approved;
                perfSum[i].percentage += perf.percentage;
                rowCountPerf[i]++;
            }
        });

        if(item.performance_summary) {
            sum.summMonthly += item.performance_summary;
            rowCountSummMonthly++;
        }

        if(item.performance_summary_yearly) {
            sum.summYearly += item.performance_summary_yearly;
            rowCountSummYearly++;
        }

        if(item.tagihan_pln !== null) {
            if(sum.plnBill === null)
                sum.plnBill = 0;
            sum.plnBill += item.tagihan_pln;
        }

        if(item.pln_saving !== null) {
            if(sum.plnSaving === null)
                sum.plnSaving = 0;
            sum.plnSaving += item.pln_saving;
            rowCountPlnSaving++;
        }

        if(item.pln_saving_percent !== null) {
            if(sum.plnSavingPercent === null)
                sum.plnSavingPercent = 0;
            sum.plnSavingPercent += item.pln_saving_percent;
            rowCountPlnSavingPercent++;
        }

        if(item.pln_saving_yoy !== null) {
            if(sum.plnSavingYoy === null)
                sum.plnSavingYoy = 0;
            sum.plnSavingYoy += item.pln_saving_yoy;
            rowCountPlnSavingYoy++;
        }

        if(item.pln_saving_yoy_percent !== null) {
            if(sum.plnSavingYoyPercent === null)
                sum.plnSavingYoyPercent = 0;
            sum.plnSavingYoyPercent += item.pln_saving_yoy_percent;
            rowCountPlnSavingYoyPercent++;
        }

        if(item.is_pue && item.pue.online !== null) {
            if(sum.pueOnline === null)
                sum.pueOnline = 0;
            sum.pueOnline += item.pue.online;
            rowCountPueOnline++;
        }

        if(item.is_ike && item.ike !== null) {
            if(sum.ike === null)
                sum.ike = 0;
            sum.ike += item.ike;
            rowCountIke++;
        }
    });

    perfSum.forEach((item, i) => {
        currItem.performance[i].has_schedule = item.has_schedule;
        currItem.performance[i].count_all = item.count_all;
        currItem.performance[i].count_approved = item.count_approved;
        currItem.performance[i].percentage = rowCountPerf[i] > 0 ? item.percentage / rowCountPerf[i] : 0;
    });
    
    currItem.performance_summary = rowCountSummMonthly > 0 ? sum.summMonthly / rowCountSummMonthly : 0;
    currItem.performance_summary_yearly = rowCountSummYearly > 0 ? sum.summYearly / rowCountSummYearly : 0;
    
    currItem.tagihan_pln = sum.plnBill;
    currItem.pln_saving = sum.plnSaving === null ? null : (sum.plnSaving / rowCountPlnSaving);
    currItem.pln_saving_percent = sum.plnSavingPercent === null ? null : (sum.plnSavingPercent / sum.plnSaving * 100);
    currItem.pln_saving_yoy = sum.plnSavingYoy === null ? null : (sum.plnSavingYoy / rowCountPlnSavingYoy);
    currItem.pln_saving_yoy_percent = sum.plnSavingYoyPercent === null ? null : (sum.plnSavingYoyPercent / sum.plnSavingYoy * 100);

    currItem.ike = sum.ike === null ? null : (sum.ike / rowCountIke);
    
    currItem.pue.online = sum.pueOnline === null ? null : (sum.pueOnline / rowCountPueOnline);
    if(currItem.pue.online === null)
        currItem.pue.isReachTarget = false;
    else if(!pueMaxTarget.value)
        currItem.pue.isReachTarget = true;
    else
        currItem.pue.isReachTarget = currItem.pue.online <= pueMaxTarget.value;
    return currItem;
};

const hasCollapseInit = ref(false);
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
            
            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.location.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.location.divre_kode);
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            const witelData = getGroupAvg(data.slice(index), "witel_kode");

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
    return groupedData;
};

const isLoading = ref(true);
const hasInit = ref(false);
const fetch = () => {
    isLoading.value = true;
    hasInit.value = true;
    gepeeReportStore.getReport(({ data }) => {
        if(data.pue_max_target)
            pueMaxTarget.value = data.pue_max_target;
        if(data.category)
            categoryList.value = data.category;
        if(data.gepee) {
            hasCollapseInit.value = false;
            collapsedDivre.value = [];
            collapsedWitel.value = [];
            tableData.value = groupData(data.gepee);
        }

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

const isPueReachTarget = item => item.pue.isReachTarget;
const formatItemNumber = (itemNumb, nullText = "-", pattern = "[value]") => {
    if(itemNumb === null)
        return nullText;
    const value = toNumberText(itemNumb);
    return pattern.replaceAll("[value]", value);
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

    if(type == "plnBill") {
        itemNumb = Number(toFixedNumber(itemNumb, 2));
        if(itemNumb < 0)
            return "tc-percentage-good";
        if(itemNumb > 0)
            return "tc-percentage-danger";
        return null;
    }
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
                        <th class="bg-success" rowspan="2">Tipe<br>Perhitungan</th>
                        <th class="bg-success" rowspan="2">Nilai IKE<br><small>(Bulan {{ selectedMonth }})</small></th>
                        <th class="bg-success" colspan="2">PUE <small>(Bulan {{ selectedMonth }} {{ selectedYear }})</small></th>
                        <th class="bg-success" colspan="4">Tagihan PLN</th>
                        <th class="bg-success" :colspan="categoryList.length+1">
                            Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)
                        </th>
                        <th rowspan="2">% Gepee Activity<br><small>(Bulan {{ selectedMonth }})</small></th>
                        <th rowspan="2">% Gepee Activity<br><small>(Tahun {{ selectedYear }})</small></th>
                    </tr>
                    <tr>
                        <th>Nilai PUE</th>
                        <th>PUE &lt;= {{ pueMaxTarget }}<br><small>(YA/TIDAK)</small></th>
                        <th class="tw-whitespace-nowrap">Rp. Tagihan PLN<br><small>(Bulan {{ selectedMonth }})</small></th>
                        <th class="tw-whitespace-nowrap">Jumlah Saving<br><small>(Dibanding bulan sebelumnya)</small></th>
                        <th class="tw-whitespace-nowrap">% Saving<br><small>(Dibanding bulan sebelumnya)</small></th>
                        <th class="tw-whitespace-nowrap">% Saving YoY<br><small>(Dibanding bulan last year)</small></th>
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
                        <td class="bg-cell-mute"></td>
                        <td class="bg-cell-mute"></td>
                        <td class="middle text-center f-w-700 text-muted">
                            {{ formatItemNumber(summaryNasional.ike) }}
                        </td>
                        <td :class="getColClassNumber('pue', summaryNasional.pue_online)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.pue_online) }}
                        </td>
                        <td class="middle text-center">
                            {{ summaryNasional.isPueReachTarget ? "YA" : "TIDAK" }}
                        </td>
                        <td class="middle text-center f-w-700 tw-whitespace-nowrap text-muted">
                            {{ formatItemNumber(summaryNasional.tagihan_pln, "-", "Rp [value]") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', summaryNasional.pln_saving)" class="middle text-center f-w-700 tw-whitespace-nowrap">
                            {{ formatItemNumber(summaryNasional.pln_saving, "-", "Rp [value]") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', summaryNasional.pln_saving)" class="middle text-center f-w-700 tw-whitespace-nowrap">
                            {{ formatItemNumber(summaryNasional.pln_saving_percent, "-", "[value]%") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', summaryNasional.pln_saving_yoy)" class="middle text-center f-w-700 tw-whitespace-nowrap">
                            {{ formatItemNumber(summaryNasional.pln_saving_yoy_percent, "-", "[value]%") }}
                        </td>
                        <td v-for="percentage in summaryNasional.performance"
                            :class="getColClassNumber('percent', percentage)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(percentage, "-", "[value]%") }}
                        </td>
                        <td class="middle text-center">-</td>
                        <td :class="getColClassNumber('percent', summaryNasional.performance_summary)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.performance_summary, "-", "[value]%") }}
                        </td>
                        <td :class="getColClassNumber('percent', summaryNasional.performance_summary_yearly)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(summaryNasional.performance_summary_yearly, "-", "[value]%") }}
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

                        <td v-if="item.type == 'sto'" class="middle text-center">{{ item.location.id_pel_pln }}</td>
                        <td v-else class="bg-cell-mute"></td>
                        
                        <td v-if="item.type == 'sto'" class="middle text-center tw-whitespace-nowrap">
                            {{ item.is_pue ? "PUE" : item.is_ike ? "IKE" : "-" }}
                        </td>
                        <td v-else class="bg-cell-mute"></td>

                        <template v-if="item.is_ike || item.type != 'sto'">
                            <td class="middle text-center">
                                {{ formatItemNumber(item.ike) }}
                            </td>
                        </template>
                        <template v-else>
                            <td class="bg-cell-mute"></td>
                        </template>

                        <template v-if="item.is_pue || item.type != 'sto'">
    
                            <td :class="getColClassNumber('pue', item.pue.online)" class="middle text-center f-w-700">
                                {{ formatItemNumber(item.pue.online) }}
                            </td>
    
                            <td class="middle text-center">{{ isPueReachTarget(item) ? "YA" : "TIDAK" }}</td>

                        </template>
                        <template v-else>
                            <td v-for="col in 2" class="bg-cell-mute"></td>
                        </template>

                        <td class="middle text-center tw-whitespace-nowrap">
                            {{ formatItemNumber(item.tagihan_pln, "-", "Rp [value]") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', item.pln_saving)" class="middle text-center tw-whitespace-nowrap">
                            {{ formatItemNumber(item.pln_saving, "-", "Rp [value]") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', item.pln_saving)" class="middle text-center tw-whitespace-nowrap">
                            {{ formatItemNumber(item.pln_saving_percent, "-", "[value]%") }}
                        </td>
                        <td :class="getColClassNumber('plnBill', item.pln_saving_yoy)" class="middle text-center tw-whitespace-nowrap">
                            {{ formatItemNumber(item.pln_saving_yoy_percent, "-", "[value]%") }}
                        </td>
                        <td v-for="category in item.performance" :class="getColClassNumber('percent', category.percentage)"
                            class="middle text-center f-w-700">
                            <span v-if="!category.has_schedule">-</span>
                            <span v-else>
                                {{ formatItemNumber(category.percentage, "-", "[value]%") }}
                            </span>
                        </td>
                        <td class="middle text-center f-w-700">-</td>
                        <td :class="getColClassNumber('percent', item.performance_summary)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(item.performance_summary, "-", "[value]%") }}
                        </td>
                        <td :class="getColClassNumber('percent', item.performance_summary_yearly)"
                            class="middle text-center f-w-700">
                            {{ formatItemNumber(item.performance_summary_yearly, "-", "[value]%") }}
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

.bg-cell-mute {
    @apply !tw-bg-zinc-100/70;
}

</style>