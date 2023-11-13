<script setup>
import { ref, computed } from "vue";
import { usePiLatenStore } from "@/stores/pi-laten";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";
import bill from "../helpers/sample-data/pln/bill";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const piLatenStore = usePiLatenStore();

const targetMonth = ref(piLatenStore.filters.month);
const divreList = ref([]);
const witelList = ref([]);

const getNasionalBillPercent = divreCode => {

    const divre = divreList.value.find(item => item.divre_kode == divreCode);
    const billDivre = divre && (divre?.bill_total?.cmp_full || 0) ? divre.bill_total.cmp_full : null;
    if(!billDivre)
        return billDivre;

    const billTotal = divreList.value.reduce((sum, item) => {
        if(item?.bill_total?.cmp_full)
            sum += item.bill_total.cmp_full;
        return sum;
    }, 0);

    return billDivre / billTotal * 100;
};

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

const getGroupAvg = (data, groupKey) => {
    
    const currItem = JSON.parse(JSON.stringify(data[0]));
    
    const bills = {};
    for(let key in currItem.bill_total) {
        bills[key] = null;
    }

    const saving = {};
    for(let key in currItem.saving) {
        saving[key] = null;
    }

    const cer = {};
    for(let key in currItem.cer) {
        cer[key] = [];
    }

    data.forEach(item => {
        if(item[groupKey] != currItem[groupKey])
            return;

        for(let key in item.bill_total) {
            if(item.bill_total[key] !== null && item.bill_total[key] !== undefined) {
                if(bills[key] === null)
                    bills[key] = 0;
                bills[key] += item.bill_total[key];
            }
        }

        for(let key in item.saving) {
            if(item.saving[key] !== null && item.saving[key] !== undefined) {
                if(saving[key] === null)
                    saving[key] = 0;
                saving[key] += item.saving[key];
            }
        }

        for(let key in item.cer) {
            if(item.cer[key] !== null && item.cer[key] !== undefined) {
                cer[key].push(item.cer[key]);
            }
        }

    });

    currItem.bill_total = bills;
    currItem.treg_bill_percent = null;

    for(let key in currItem.saving) {
        currItem.saving[key] = saving[key];
    }

    for(let key in currItem.cer) {
        currItem.cer[key] = null;
        if(cer[key].length > 0)
            currItem.cer[key] = cer[key].reduce((sum, item) => sum += item, 0) / cer[key].length;
    }

    return currItem;
};

const hasCollapseInit = ref(false);
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
        
        type = "witel";
        title = "WILAYAH " + item.witel_name;
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

        divreList.value = data.treg_list ? data.treg_list : [];

        if(data.witel_list) {
            hasCollapseInit.value = false;
            collapsedDivre.value = [];
            collapsedWitel.value = [];
            witelList.value = groupData(data.witel_list);
        } else
            witelList.value = [];

        isLoading.value = false;
    });

};

defineExpose({ fetch });

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
        return "tc-pue-efficient";
    return "tc-pue-inefficient";
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
        <div v-else-if="witelList.length < 1" class="px-4 py-3 border">
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
                        <tr v-for="item in witelList" :class="getRowClass(item)">
                            <td class="sticky-column">
    
                                <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                    <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)"
                                        :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }"
                                        class="btn btn-circle btn-light p-0 btn-collapse-row">
                                        <VueFeather type="chevron-right" size="1rem" />
                                    </button>
                                    <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                                </div>
    
                                <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                    <small class="fw-semibold" :class="{ 'ms-5': level == 'nasional' }">{{ item.title }}</small>
                                </p>
                                
                            </td>

                            <td>
                                {{ formatIdr(item.bill_total.cmp_full, "-") }}
                            </td>

                            <td>
                                {{ formatIdr(item.bill_total.cmp, "-") }}
                            </td>

                            <td>
                                {{ formatIdr(item.bill_total.src, "-") }}
                            </td>

                            <td v-if="item.type != 'divre'">
                                {{ formatPercent(item.treg_bill_percent, "-") }}
                            </td>
                            <td v-else>
                                {{ formatPercent(getNasionalBillPercent(item.divre_kode), "-") }}
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
                                {{ formatPercent(item.cer.src_real, "-") }}
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4 class="mb-0">Keterangan</h4>
                        </div>
                        <div class="card-body">
                            <ol class="tw-pl-8 tw-list-decimal">
                                <li>
                                    Cost Energy Ratio (CER) : Nilai perbandingan antara jumlah cost energy YoY.
                                    Semakin besar ratio semakin besar cost operasional.
                                    Usulan Setting target <strong>&lt; 90%</strong> (Ref {{ yearTarget.cmp1 }}) atau <strong>&lt; 95%</strong> (Ref {{ yearTarget.cmp2 }}).
                                </li>
                                <li>
                                    Cost Energy Efficiency (CEF) : Nilai perbandingan antara jumlah efisiensi terhadap cost.
                                    Semakin besar ratio semakin besar cost operational. Usulan setting target <strong>-10%</strong>
                                    (Ref {{ yearTarget.cmp1 }}) atau <strong>-5%</strong> (Ref {{ yearTarget.cmp2 }}).
                                </li>
                                <li>Implementasi mulai September 2023.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div> -->
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