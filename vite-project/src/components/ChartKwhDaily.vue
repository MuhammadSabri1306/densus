<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const props = defineProps({
    rtuCode: { required: true }
});

const chartOptions = {
    chart: {
        animations: { enabled: false }
    },
    xaxis: {
        type: "datetime"
    },
    title: {
        text: "KwH Daily Last 3 Day",
        align: "left"
    },
    curve: "smooth",
    colors: [dtColors.primary],
    dataLabels: {
        enabled: false
    }
};

const monitoringStore = useMonitoringStore();
const dataChart = await monitoringStore.getChartDataDaily(props.rtuCode);

const series = [{
    data: dataChart.map(item => {
        const timestamp = new Date(item.timestamp);
        const val = Number(item.kwh_value);
        return [ timestamp, val ];
    })
}];
</script>
<template>
    <div class="card income-card">
        <div class="card">
            <div class="card-header pb-0">
                <h5>KwH Usage Chart</h5>
            </div>
            <div class="card-body">
                <VueApexCharts width="100%" height="380px" type="area" :options="chartOptions" :series="series" />
            </div>
        </div>
    </div> 
</template>