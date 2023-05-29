<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@stores/pue-v2";
import { groupByLocation } from "@helpers/location-group-pue";
import { toFixedNumber } from "@helpers/number-format";
import { getPueBgClass } from "@helpers/pue-color";
import { useCollapseRow } from "@helpers/collapse-row";
import Skeleton from "primevue/skeleton";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

const props = defineProps({
    level: String
});

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const tableData = ref([]);
const yearTarget = ref(null);

const pueStore = usePueV2Store();
const isLoading = ref(true);
pueStore.getStoValue(({ data }) => {
    if(data.stoValue) {
        tableData.value = groupByLocation({
            data: data.stoValue,
            averagedKeys: ["currDay", "currWeek", "currMonth"],
            divre: {
                groupKey: "divre_kode",
                checker: isMatch => isMatch && props.level == "nasional",
                formatTitle: item => item.divre_name
            },
            witel: {
                groupKey: "witel_kode",
                checker: isMatch => isMatch && props.level != "witel",
                formatTitle: item => "WILAYAH " + item.witel_name
            },
            sto: { formatTitle: item => item.rtu_name }
        });
    }

    if(data.timestamp) {
        const date = new Date(data.timestamp);
        if(date) yearTarget.value = date.getFullYear();
    }

    isLoading.value = false;
});

const showDialogExport = ref(false);
const getDetailUrl = item => {
    const paramsKey = item.type == "sto" ? "rtu" : item.type;
    const paramsCode = item[paramsKey + "_kode"];
    return `/pue/online/${ paramsKey }/${ paramsCode }`;
};
</script>
<template>
    <div class="card border-0">
        <div class="card-header border-top border-start border-end d-flex align-items-center">
            <h5 v-if="yearTarget">Tabel nilai PUE tahun {{ yearTarget }}</h5>
            <h5 v-else>Tabel nilai PUE tahun ini</h5>
            <button type="button" @click="showDialogExport = true" class="btn-icon ms-auto px-3">
                <VueFeather type="download" size="1em" />
                <span class="ms-2">Export</span>
            </button>
        </div>
        <div v-if="isLoading" class="card-body">
            <div v-for="i in 6" class="row g-4 mb-3">
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
                <div class="col"><Skeleton /></div>
            </div>
        </div>
        <div v-else class="table-responsive bg-white">
            <table class="table table-bordered table-head-primary table-pue">
                <thead>
                    <tr>
                        <th rowspan="2" class="bg-success">Lingkup Kerja</th>
                        <th colspan="3" class="bg-success">Nilai Rata-Rata PUE</th>
                        <th rowspan="2" class="bg-success"></th>
                    </tr>
                    <tr>
                        <th>Hari ini</th><th>Minggu ini</th><th>Bulan ini</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in tableData" :class="{ 'row-collapsed': ((item.type == 'sto' && collapsedWitel.indexOf(item.witel_kode) >= 0) || (item.type == 'sto' && collapsedDivre.indexOf(item.divre_kode) >= 0)) || (item.type == 'witel' && collapsedDivre.indexOf(item.divre_kode) >= 0) }">
                        <td>
                            <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)"
                                    :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)"
                                    :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0, 'ms-4': level == 'nasional' }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <div v-else class="px-4 py-1 tw-whitespace-nowrap">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ item.title }}</small>
                            </div>
                        </td>
                        <td :class="getPueBgClass(toFixedNumber(item.currDay, 2))"
                            class="text-end middle f-w-700">{{ toFixedNumber(item.currDay, 2) }}</td>
                        <td :class="getPueBgClass(toFixedNumber(item.currWeek, 2))"
                            class="text-end middle f-w-700">{{ toFixedNumber(item.currWeek, 2) }}</td>
                        <td :class="getPueBgClass(toFixedNumber(item.currMonth, 2))"
                            class="text-end middle f-w-700">{{ toFixedNumber(item.currMonth, 2) }}</td>
                        <td class="action-column">
                            <RouterLink :to="getDetailUrl(item)" class="btn btn-sm btn-primary">Detail</RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/pue" title="Export Data PUE Online"
            useDivre useWitel useYear requireYear @close="showDialogExport = false" />
    </div>
</template>
<style scoped>

.action-column {
    width: 1%;
}

</style>