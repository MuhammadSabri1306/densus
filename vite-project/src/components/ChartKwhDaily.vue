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
        type: "datetime",
        labels: { datetimeUTC: false }
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
// const dataChart = await monitoringStore.getChartDataDaily(props.rtuCode);

const test = () => {
    return new Promise(resolve => {
        setTimeout(async () => {
            const data = await monitoringStore.getChartDataDaily(props.rtuCode);
            resolve(data);
        }, 2000)
    });
}
const dataChart = await test();

// if(Array.isArray(dataChart) && dataChart.length > 0) {
//     const startDate = new Date(dataChart[0].timestamp);
//     const endDate = new Date(dataChart[dataChart.length - 1].timestamp);

//     if(startDate)
//         chartOptions.xaxis.min = startDate.getTime();
//     if(endDate)
//         chartOptions.xaxis.max = endDate.getTime();
//     console.log(endDate, endDate.getTime())
// }

const getTimestamp = timestamp => {
    const date = new Date(timestamp);
    const dateInWIB = date.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });

    const utcDate = new Date(dateInWIB);
    return utcDate.getTime();
};

const series = [{
    data: dataChart.map(item => {
        const timestamp = getTimestamp(item.timestamp);
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