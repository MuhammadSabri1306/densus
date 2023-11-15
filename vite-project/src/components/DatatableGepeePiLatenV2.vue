<script setup>
import { ref, computed } from "vue";
import { usePiLatenStore } from "@/stores/pi-laten";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const piLatenStore = usePiLatenStore();

const targetMonth = ref(piLatenStore.filters.month);
const divreList = ref([]);
const witelList = ref([]);
const tableData = ref([]);
const nasionalData = ref(null);

const locMode = computed(() => piLatenStore.locationMode);

const filterYear = piLatenStore.filters.year;
const yearTarget = ref({
    src: filterYear,
    cmp: filterYear - 1
});

const monthRangeTitle = computed(() => {
    const endMonth = targetMonth.value;
    if(endMonth < 1 || endMonth > 12) {
        return {
            start: null,
            end: null,
            full: null
        };
    }

    const startMonthName = piLatenStore.monthList[0].name.slice(0, 3);
    const endMonthName = piLatenStore.monthList[endMonth - 1].name.slice(0, 3);
    const fullMonthName = endMonth === 1 ? startMonthName : `${ startMonthName } - ${ endMonthName }`;
    return {
        start: startMonthName,
        end: endMonthName,
        full: fullMonthName
    };
});

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = piLatenStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const roundLikePHP = (num, dec) => {
    const numSign = num >= 0 ? 1 : -1;
    return parseFloat((Math.round((num * Math.pow(10, dec)) + (numSign * 0.0001)) / Math.pow(10, dec)).toFixed(dec));
}

const getGroupDivre = divreCode => {
    const matchedDivre = divreList.value.find(item => item.location.divre_kode == divreCode);
    if(!matchedDivre)
        return null;

    const currDivre = JSON.parse(JSON.stringify(matchedDivre));
    currDivre.is_filter_applied = true;

    const avg = { srcReal: [] };
    const sum = {
        savingTarget: null,
        savingValue: null
    };
    
    witelList.value.forEach(witel => {
        if(witel.location.divre_kode != divreCode)
            return;

        if(witel.saving.target) {
            if(sum.savingTarget === null)
                sum.savingTarget = 0;
            sum.savingTarget += witel.saving.target;
        }

        if(witel.saving.value) {
            if(sum.savingValue === null)
                sum.savingValue = 0;
            sum.savingValue += witel.saving.value;
        }

        if(witel.cer.src_real)
            avg.srcReal.push(witel.cer.src_real);
    });

    currDivre.saving = {
        target: sum.savingTarget,
        value: sum.savingValue,
        achievement: roundLikePHP(sum.savingTarget + sum.savingValue, 2)
    };

    if(currDivre.saving.achievement <= 0)
        currDivre.saving.achievement = null;

    currDivre.cer = { src_real: null };
    if(avg.srcReal.length > 0) {
        currDivre.cer.src_real = avg.srcReal.reduce((sum, item) => sum + item, 0) / avg.srcReal.length;
    }

    return currDivre;

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
            // const divreData = getGroupAvg(data.slice(index), "divre_kode");
            const divreData = getGroupDivre(item.location.divre_kode);
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.location.divre_kode;
            
            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.location.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.location.divre_kode);
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            // const witelData = getGroupAvg(data.slice(index), "witel_kode");
            const witelData = witelList.value.find(witelItem => witelItem.location.witel_kode == item.location.witel_kode);

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.location.witel_kode;

            const hasWitelCollapsed = collapsedWitel.value.indexOf(item.location.witel_kode) >= 0;
            if(!hasCollapseInit.value && !hasWitelCollapsed)
                toggleRowCollapse("witel", item.location.witel_kode);
        }
        
        type = "sto";
        title = locMode.value == "gepee" ? item.location.sto_name : item.location.name;
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
    piLatenStore.getPiV2(({ data }) => {

        if(data.filter_month)
            targetMonth.value = data.filter_month;

        if(data.years) {
            const years = yearTarget.value;
            if(data.years.source)
                years.src = data.years.source;
            if(data.years.comparison_1)
                years.cmp = data.years.comparison;
            yearTarget.value = years;
        }

        nasionalData.value = data.nasional_data ? data.nasional_data : [];
        divreList.value = data.treg_list ? data.treg_list : [];
        witelList.value = data.witel_list ? data.witel_list : [];

        if(data.sto_list) {
            hasCollapseInit.value = false;
            collapsedDivre.value = [];
            collapsedWitel.value = [];
            tableData.value = groupData(data.sto_list);
        } else
            tableData.value = [];

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
}

const formatIdr = (numb, defaultText = null) => {
    if(numb === null || numb == undefined)
        return defaultText;

    const isNegative = numb < 0;
    if(isNegative)
        numb *= -1;

    const text = toNumberText(numb, 2);
    return isNegative ? "Rp -" + text : "Rp " + text;
};

const formatPercent = (numb, defaultText = null) => {
    if(numb === null || numb == undefined)
        return defaultText;

    const numbText = toNumberText(numb, 2);
    return `${ numbText }%`;
};

const getSavingValueClass = savingValue => {
    if(savingValue === null || savingValue === undefined)
        return null;
    if(savingValue >= 0)
        return "tc-pue-inefficient";
    return "tc-pue-efficient";
};

const getSavingAchvClass = achvValue => {
    if(achvValue === undefined)
        return null;
    if(achvValue === null)
        return "tc-pue-efficient";
    return "tc-pue-inefficient";
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
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div v-else>
            <div class="table-responsive table-freeze bg-white pb-3 mb-4">

                <table class="table table-bordered table-head-primary table-collapsable table-pi-laten">
                    <thead>
                        <tr>
                            <th class="bg-success sticky-column">Lingkup Kerja</th>
                            <th class="bg-success">
                                Full Tahun {{ yearTarget.cmp }}
                            </th>
                            <th class="bg-success">
                                {{ monthRangeTitle.full }} {{ yearTarget.cmp }}
                            </th>
                            <th class="bg-success">
                                {{ monthRangeTitle.full }} {{ yearTarget.src }}
                            </th>
                            <th class="bg-success">
                                Presentasi<br>Kontribusi<br>
                                <small>Tagihan {{ yearTarget.cmp }}</small>
                            </th>
                            <th class="bg-success">Target Saving</th>
                            <th class="bg-success">
                                Saving {{ monthRangeTitle.full }}<br>
                                <small>Tahun {{ yearTarget.src }} - {{ yearTarget.cmp }}</small>
                            </th>
                            <th class="bg-success">Selisih Target & Saving</th>
                            <th class="bg-success">
                                CER<br>
                                <small>{{ monthRangeTitle.end }} (Real)</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="nasionalData">
                        
                            <td class="sticky-column !tw-bg-[#24695c] text-white">
                                <b class="px-3">SUMMARY NASIONAL</b>
                            </td>

                            <td v-if="nasionalData?.bill.cmp_full !== undefined">
                                {{ formatIdr(nasionalData.bill.cmp_full) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.bill?.cmp !== undefined">
                                {{ formatIdr(nasionalData.bill.cmp) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.bill?.src !== undefined">
                                {{ formatIdr(nasionalData.bill.src) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData.bill_fraction !== undefined">
                                {{ formatPercent(nasionalData.bill_fraction) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.saving?.target !== undefined">
                                {{ formatIdr(nasionalData.saving.target) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.saving?.value !== undefined" class="f-w-700"
                                :class="getSavingValueClass(nasionalData.saving.value)">
                                {{ formatIdr(nasionalData.saving.value) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.saving?.achievement !== undefined" class="f-w-700"
                                :class="getSavingAchvClass(nasionalData.saving.achievement)">
                                {{ formatIdr(nasionalData.saving.achievement, "-") }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>

                            <td v-if="nasionalData?.cer?.src_real !== undefined">
                                {{ formatPercent(nasionalData?.cer?.src_real) }}
                            </td>
                            <td v-else class="bg-cell-mute"></td>
                        
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

                            <td>
                                {{ formatIdr(item.bill.cmp_full, "-") }}
                            </td>

                            <td>
                                {{ formatIdr(item.bill.cmp, "-") }}
                            </td>

                            <td>
                                {{ formatIdr(item.bill.src, "-") }}
                            </td>

                            <td>
                                {{ formatPercent(item.bill_fraction, "-") }}
                            </td>

                            <td>
                                {{ formatIdr(item.saving.target, "-") }}
                            </td>

                            <td :class="getSavingValueClass(item.saving.value)" class="f-w-700">
                                {{ formatIdr(item.saving.value, "-") }}
                            </td>

                            <td :class="getSavingAchvClass(item.saving.achievement)" class="f-w-700">
                                {{ formatIdr(item.saving.achievement, "-") }}
                            </td>

                            <td>
                                {{ formatPercent(item?.cer?.src_real, "-") }}
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<style scoped>

.table-pi-laten th {
    text-align: center;
    white-space: nowrap;
}

.table-pi-laten td {
    vertical-align: middle;
    white-space: nowrap;
}

.table-pi-laten td:not(:first-child) {
    text-align: right;
}

.bg-cell-mute {
    @apply !tw-bg-zinc-100/70;
}

</style>