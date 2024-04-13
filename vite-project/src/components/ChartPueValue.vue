<script setup>
import { ref } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { toFixedNumber } from "@/helpers/number-format"
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const emit = defineEmits(["loaded"]);

const chartOptions = {
    chart: {
        type: "area"
    },
    yaxis: {
        min: 1,
        max: 4,
        labels: {
            formatter: val => toFixedNumber(val, 2)
        }
    },
    xaxis: {
        type: "datetime"
    },
    stroke: {
        width: [2, 0.5, 0.5, 0.5, 0.5],
        dashArray: [0, 3, 3, 3, 3]
    },
    fill: {
        type: "solid",
        opacity: 0.5,
        colors: [dtColors.info, "transparent", "transparent", "transparent", "transparent"]
    },
    curve: "smooth",
    colors: [dtColors.info, dtColors.primary, dtColors.success, dtColors.warning, dtColors.danger],
    dataLabels: {
        enabled: false
    }
};

const series = ref([]);
const setupSeries = pueValues => {
    const seriesItem = [
        { name: "Nilai PUE", limit: null },
        { name: "Optimize", limit: 1.6 },
        { name: "Efficient", limit: 2 },
        { name: "Average", limit: 2.4 },
        { name: "Inefficient", limit: 3 }
    ];

    return seriesItem.map(({ name, limit }) => {
        const data = pueValues.map(pueItem => {
            const timestamp = new Date(pueItem.timestamp);
            const value = limit || Number(pueItem.pue_value);
            return [ timestamp, value ];
        });
        return { name, data };
    });
};

const pueStore = usePueV2Store();
const isLoading = ref(true);
pueStore.fetchChartData(success => {
    if(success && pueStore.chartData)
        series.value = setupSeries(pueStore.chartData);
    isLoading.value = false;
});
</script>
<template>
    <div class="card">
        <div class="card-header pb-0 d-flex">
            <div class="card-title">
                <h5>Grafik Nilai PUE</h5>
                <p class="mb-0">Dalam Seminggu terakhir</p>
            </div>
        </div>
        <div class="card-body">
            <VueApexCharts width="100%" height="380px" :options="chartOptions" :series="series" />
            <div v-if="isLoading" class="position-absolute top-0 left-0 w-100 h-100 d-flex justify-content-center align-items-center">
                <VueFeather type="loader" size="3rem" stroke-width="1.5" class="text-muted" animation="spin" animation-speed="slow" />
            </div>
        </div>
    </div>
</template>