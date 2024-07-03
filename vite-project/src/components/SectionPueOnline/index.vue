<script setup>
import { ref } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { backendUrl } from "@/configs/base";
import CardLocation from "./CardLocation.vue";
import CardLatestPue from "./CardLatestPue.vue";
import CardHighestPue from "./CardHighestPue.vue";
import Chart from "./Chart.vue";
import CardAverages from "./CardAverages.vue";
import CardPerformances from "./CardPerformances.vue";
import Datatable from "./Datatable.vue";
import DialogDetailRtu from "./DialogDetailRtu.vue";

const isLoading = ref(false);
const fetchLevel = ref(null);
const chartData = ref([]);
const latestPue = ref(null);
const highestPue = ref(null);
const averages = ref(null);
const performances = ref(null);

const datatableElm = ref(null);

const pueStore = usePueV2Store();
const hasInit = ref(false);
const fetch = () => {
    if(!hasInit.value)
        hasInit.value = true;
    isLoading.value = true;
    pueStore.getPueOnlineMonitoringData(data => {
        fetchLevel.value = data?.level || null;
        chartData.value = data?.chart_data || [];
        latestPue.value = data?.latest_pue || null;
        highestPue.value = data?.highest_pue || null;
        averages.value = data?.averages || null;
        performances.value = data?.performances || null;

        datatableElm.value.setData(data?.tables_data || []);
        isLoading.value = false;
    });
};

defineExpose({ fetch });

const showDialogDetail = ref(false);
const detailRtuCode = ref(null);

const onDatatableDetail = (rtuCode) => {
    detailRtuCode.value = rtuCode;
    showDialogDetail.value = true;
};

const onDialogDetailHide = () => {
    showDialogDetail.value = false;
    detailRtuCode.value = null;
};
</script>
<template>
    <section>
        <div v-if="!hasInit">
            <h4 class="text-center">Cari Lokasi PUE</h4>
        </div>
        <div v-else class="row align-items-end">
            <div class="col-md-4">
                <CardLocation :isLoading="isLoading" :locs="fetchLevel" />
                <CardLatestPue :isLoading="isLoading" :latestPue="latestPue" />
                <CardHighestPue :isLoading="isLoading" :highestPue="highestPue" />
            </div>
            <div class="col-md-8">
                <Chart :isLoading="isLoading" :chartData="chartData" />
            </div>
            <div class="col-md-6">
                <CardAverages :isLoading="isLoading" :averages="averages" />
            </div>
            <div class="col-md-6">
                <CardPerformances :isLoading="isLoading" :performances="performances" />
            </div>
        </div>
        <Datatable v-show="hasInit" ref="datatableElm" :isLoading="isLoading" @detail="onDatatableDetail" />
        <DialogDetailRtu v-if="showDialogDetail" :rtuCode="detailRtuCode" @close="onDialogDetailHide" />
    </section>
</template>