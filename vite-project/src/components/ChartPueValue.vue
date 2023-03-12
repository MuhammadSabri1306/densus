<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const props = defineProps({
    pueValues: { type: Array, required: true }
});

const chartOptions = {
    chart: {
        type: "area"
    },
    yaxis: {
        min: 1,
        max: 4
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
    colors: [dtColors.info, "#aaaaaa", dtColors.success, dtColors.primary, dtColors.danger],
    dataLabels: {
        enabled: false
    }
};

const formatPueData = (val = null) => {
    return props.pueValues.map(item => {
        const timestamp = new Date(item.timestamp);
        const value = val || Number(item.pue_value);
        return [ timestamp, value ];
    });
};

const series = [
    { name: "Nilai PUE", data: formatPueData() },
    { name: "Optimize", data: formatPueData(1.6) },
    { name: "Efficient", data: formatPueData(2) },
    { name: "Average", data: formatPueData(2.4) },
    { name: "Inefficient", data: formatPueData(3) },
];
</script>
<template>
    <VueApexCharts width="100%" height="380px" :options="chartOptions" :series="series" />
</template>