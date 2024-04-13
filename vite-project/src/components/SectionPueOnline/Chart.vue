<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@/helpers/number-format"
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    chartData: { type: Array, required: true }
});

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

const seriesItem = [
    { name: "Nilai PUE", limit: null },
    { name: "Optimize", limit: 1.6 },
    { name: "Efficient", limit: 2 },
    { name: "Average", limit: 2.4 },
    { name: "Inefficient", limit: 3 }
];

const series = computed(() => {
    const srcData = props.chartData;
    if(srcData.length < 1)
        return [];

    const pueSeries = srcData
        .map(item => {
            const timestamp = new Date(item.timestamp);
            const value = Number(item.pue_value);
            return [ timestamp, value ];
        })
        .sort((a, b) => a[0] - b[0]);
    
    const firstTimestamp = pueSeries[0][0];
    const lastTimestamp = pueSeries[pueSeries.length - 1][0];
    const buildLimiterSeries = limit => {
        return [
            [ firstTimestamp, limit ],
            [ lastTimestamp, limit ],
        ];
    };

    return [
        { name: "Nilai PUE", data: pueSeries },
        { name: "Optimize", data: buildLimiterSeries(1.6) },
        { name: "Efficient", data: buildLimiterSeries(2) },
        { name: "Average", data: buildLimiterSeries(2.4) },
        { name: "Inefficient", data: buildLimiterSeries(3) }
    ];
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