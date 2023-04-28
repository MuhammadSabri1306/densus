<script setup>
import { computed } from "vue";
import { groupByLocation } from "@helpers/location-group-pue";
import { toFixedNumber } from "@helpers/number-format";
import { getPueBgClass } from "@helpers/pue-color";
import { useCollapseRow } from "@helpers/collapse-row";

const props = defineProps({
    list: { type: Array, default: [] },
    level: String
});

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const tableData = computed(() => {
    const list = props.list;
    const test = groupByLocation({
        data: list,
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
    console.log(test);
    return test;
});

const getDetailUrl = item => {
    const paramsKey = item.type == "sto" ? "rtu" : item.type;
    const paramsCode = item[paramsKey + "_kode"];
    return `/pue/${ paramsKey }/${ paramsCode }`;
};
</script>
<template>
    <div class="table-responsive bg-white">
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
                        <div v-else class="px-4 py-1 tw-whitespace-nowrap"><small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small></div>
                    </td>
                    <td :class="getPueBgClass(toFixedNumber(item.currDay, 2))" class="text-end middle f-w-700">{{ toFixedNumber(item.currDay, 2) }}</td>
                    <td :class="getPueBgClass(toFixedNumber(item.currWeek, 2))" class="text-end middle f-w-700">{{ toFixedNumber(item.currWeek, 2) }}</td>
                    <td :class="getPueBgClass(toFixedNumber(item.currMonth, 2))" class="text-end middle f-w-700">{{ toFixedNumber(item.currMonth, 2) }}</td>
                    <td class="action-column">
                        <RouterLink :to="getDetailUrl(item)" class="btn btn-sm btn-primary">Detail</RouterLink>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<style scoped>

.action-column {
    width: 1%;
}

</style>