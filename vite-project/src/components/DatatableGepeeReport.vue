<script setup>
import { ref, computed } from "vue";
import { useGepeeReportStore } from "@/stores/gepee-report";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

defineEmits(["showCategory"]);

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const gepeeReportStore = useGepeeReportStore();

const tableData = ref([]);
const categoryList = ref([]);
const pueLowLimit = ref(null);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = gepeeReportStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[0]));
    let rowCount = 0;
    const sum = {
        exc: [],
        pue: { online: 0, offline: 0 }
    };

    data.forEach(item => {
        if(item.location[groupKey] != currItem.location[groupKey])
            return;

        for(let i=0; i<item.performance.length; i++) {
            if(sum.exc.length < (i + 1)) {
                sum.exc.push({
                    countAll: 0,
                    countApproved: 0,
                });
            }

            sum.exc[i].countAll += item.performance[i].count_all;
            sum.exc[i].countApproved += item.performance[i].count_approved;
        }

        if(item.pue.online)
            sum.pue.online += item.pue.online;
        if(item.pue.offline)
            sum.pue.offline += item.pue.offline;
        rowCount++;

    });

    sum.exc.forEach((sumItem, index) => {

        currItem.performance[index].count_all = sumItem.countAll;
        currItem.performance[index].count_approved = sumItem.countApproved;
        currItem.performance[index].percentage = (sumItem.countAll > 0)
            ? sumItem.countApproved / sumItem.countAll * 100
            : 0;

    });

    currItem.pue.online = rowCount > 0 ? sum.pue.online / rowCount : null;
    currItem.pue.offline = rowCount > 0 ? sum.pue.offline / rowCount : null;

    const pueValue = currItem.pue.online !== null ? currItem.pue.online
        : currItem.pue.offline !== null ? currItem.pue.offline
        : 0;
    currItem.pue.isReachTarget = pueLowLimit.value === null ? false
        : pueValue > pueLowLimit.value;

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
        title = item.location.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
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

const formatItemNumber = itemNumb => {
    if(itemNumb === null)
        return "-";
    return toNumberText(itemNumb);
};

const isLocationOnline = item => item.type == "sto" && item.location.is_online;
const isPueReachTarget = item => item.type == "sto" && item.pue.isReachTarget;
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
                        <th class="bg-success" rowspan="2">PUE &lt;= {{ pueLowLimit }}<br><small>YA/TIDAK</small></th>
                        <th class="bg-success" rowspan="2">Tagihan PLN<br><small>(Tren Bulanan)</small></th>
                        <th class="bg-success" :colspan="categoryList.length+1">Presentase Pencapaian Aktivitas GePEE (Dihitung 100% jika sudah dilaksanakan)</th>
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
                        <td v-if="item.type != 'sto'" colspan="4"></td>
                        <template v-else>
                            <td class="middle text-center">{{ item.location.id_pel_pln }}</td>
                            <td class="middle text-center">{{ formatItemNumber(item.pue.offline) }}</td>
                            <td class="middle text-center">{{ formatItemNumber(item.pue.online) }}</td>
                            <td class="middle text-center">{{ isLocationOnline(item) ? "ONLINE" : "OFFLINE" }}</td>
                        </template>
                        <td class="middle text-center">{{ isPueReachTarget(item) ? "TIDAK" : "YA" }}</td>
                        <td class="middle text-center">-</td>
                        <td v-for="category in item.performance" class="middle text-center">{{ formatItemNumber(category.percentage) }}%</td>
                        <td class="middle text-center">-</td>
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