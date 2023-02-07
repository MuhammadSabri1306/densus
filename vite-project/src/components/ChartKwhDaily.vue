<script setup>
import http from "@/helpers/http-common";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    rtuCode: { required: true }
});

const colors = {
    primary: "#24695c",
    secondary: "#ba895d"
};

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
    colors: [colors.primary],
    dataLabels: {
        enabled: false
    }
};

let dataChart = null;
try {
    let response = await http.get("/monitoring/chartdatadaily/" + props.rtuCode);
    dataChart = response.data["chartdata_daily"];
} catch(err) {
    console.error(err);
}

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