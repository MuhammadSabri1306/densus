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

const tableData = ref([]);

const filterYear = piLatenStore.filters.year;
const yearTarget = ref({
    src: filterYear,
    cmp1: filterYear - 4,
    cmp2: filterYear - 1
});

const yearTitle = computed(() => {
    const years = yearTarget.value;
    return [ years.cmp1, years.cmp2, years.src ];
});

const yearDiffTitle = computed(() => {
    const years = yearTarget.value;
    const src = years.src.toString().slice(2);
    const cmp1 = years.cmp1.toString().slice(2);
    const cmp2 = years.cmp2.toString().slice(2);
    return [ `\`${ src } - \`${ cmp1 }`, `\`${ src } - \`${ cmp2 }` ];
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
    for(let year in currItem.bills) {
        bills[year] = null;
    }

    data.forEach(item => {
        if(item.location[groupKey] != currItem.location[groupKey])
            return;
        
        for(let year in item.bills) {
            if(item.bills[year] !== null && item.bills[year] !== undefined) {
                if(bills[year] === null)
                    bills[year] = 0;
                bills[year] += item.bills[year];
            }
        }
    });

    currItem.bills = bills;
    currItem.saving.forEach((item, index) => {

        const srcYear = item.src_year;
        const cmpYear = item.cmp_year;
        let saving = null;
        let cer = null;
        let cef = null;
        if(bills[srcYear] !== null && bills[cmpYear] !== null) {

            saving = roundLikePHP( bills[srcYear] - bills[cmpYear], 2 );
            cer = roundLikePHP( 100 + (saving / bills[cmpYear] * 100), 2 );
            cef = roundLikePHP( cer - 100, 2 );

        }

        currItem.saving[index].value = saving;
        currItem.cer[index].value = cer;
        currItem.cef[index].value = cef;

    });
    
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
    piLatenStore.getPi(({ data }) => {

        if(data.list) {
            hasCollapseInit.value = false;
            collapsedDivre.value = [];
            collapsedWitel.value = [];
            tableData.value = groupData(data.list);
        }

        if(data.years) {
            const years = yearTarget.value;
            if(data.years.source)
                years.src = data.years.source;
            if(data.years.comparison_1)
                years.cmp1 = data.years.comparison_1;
            if(data.years.comparison_2)
                years.cmp2 = data.years.comparison_2;
            yearTarget.value = years;
        }

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

const formatIdr = numb => {
    const isNegative = numb < 0;
    if(isNegative)
        numb *= -1;
    const text = "Rp " + toNumberText(numb, 2);
    return isNegative ? "-" + text : text;
};

const isSavingOk = (cerValue) => {
    if(cerValue <= 0)
        return "tc-pue-efficient";
    return "tc-pue-inefficient";
};

const isCerOk = (cerValue, year) => {
    const target = (year == yearTarget.value.cmp1) ? 90
        : (year == yearTarget.value.cmp2) ? 95
        : null;

    if(!target)
        return;
    if(cerValue < target)
        return "tc-pue-efficient";
    return "tc-pue-inefficient";
};

const isCefOk = (cefValue, year) => {
    const target = (year == yearTarget.value.cmp1) ? 10
        : (year == yearTarget.value.cmp2) ? 5
        : null;

    if(!target)
        return;
    if(-cefValue > target)
        return "tc-pue-efficient";
    return "tc-pue-inefficient";
};

const targetText = (type, compYearNumber) => {
    const years = yearTarget.value;
    if(type == "cer" && compYearNumber === 1)
        return "< 90";
    if(type == "cer" && compYearNumber === 2)
        return "< 95";
    if(type == "cef" && compYearNumber === 1)
        return "> (-10)";
    if(type == "cef" && compYearNumber === 2)
        return "> (-5)";
    return null;
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
                            <th class="bg-success sticky-column" rowspan="2">Lingkup Kerja</th>
                            <th class="bg-success" colspan="3">Tahun</th>
                            <th class="bg-success" colspan="2">Saving</th>
                            <th class="bg-success" colspan="2">CER<br><small>(Cost Energy Ratio)</small></th>
                            <th class="bg-success" colspan="2">CEF<br><small>(Cost Energy Efficiency)</small></th>
                        </tr>
                        <tr>
                            <th v-for="year in yearTitle">{{ year }}</th>
                            <th v-for="year in yearDiffTitle">{{ year }}</th>
                            <th v-for="(year, index) in yearDiffTitle" class="tw-leading-none">
                                {{ year }}
                                <template v-if="targetText('cer', index+1)">
                                    <br><small>Target {{ targetText('cer', index+1) }}</small>
                                </template>
                            </th>
                            <th v-for="(year, index) in yearDiffTitle" class="tw-leading-none">
                                {{ year }}
                                <template v-if="targetText('cef', index+1)">
                                    <br><small>Target {{ targetText('cef', index+1) }}</small>
                                </template>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
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
    
                            <template v-for="year in yearTitle">
                                <td v-if="item.bills[year] === null" class="text-center f-w-700">-</td>
                                <td v-else class="text-end tw-whitespace-nowrap">{{ formatIdr(item.bills[year]) }}</td>
                            </template>
    
                            <template v-for="saving in item.saving">
                                <td v-if="saving.value === null" class="text-center f-w-700">-</td>
                                <td v-else class="text-end tw-whitespace-nowrap f-w-700"
                                    :class="isSavingOk(saving.value)">{{ formatIdr(saving.value) }}</td>
                            </template>
    
                            <template v-for="cer in item.cer">
                                <td v-if="cer.value === null" class="text-center f-w-700">-</td>
                                <td v-else class="text-end f-w-700 tw-whitespace-nowrap"
                                    :class="isCerOk(cer.value, cer.cmp_year)">
                                    {{ toNumberText(cer.value, 2) }}%
                                </td>
                            </template>
    
                            <template v-for="cef in item.cef">
                                <td v-if="cef.value === null" class="text-center f-w-700">-</td>
                                <td v-else class="text-end f-w-700 tw-whitespace-nowrap"
                                    :class="isCefOk(cef.value, cef.cmp_year)">
                                    {{ toNumberText(cef.value, 2) }}%
                                </td>
                            </template>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row justify-content-center">
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
}

.bg-cell-mute {
    @apply !tw-bg-zinc-100/70;
}

</style>