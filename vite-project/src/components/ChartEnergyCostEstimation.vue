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

let dataEstimation = null;
let degtabledata = null;
let tabledata = null;
try {
    let response = await http.get("/monitoring/degtabledata/" + props.rtuCode);
    degtabledata = response.data.degtabledata;
    const totalGenset = degtabledata.chart["Total_genset"];
    
    response = await http.get("/monitoring/tabledata/" + props.stoCode);
    tabledata = response.data.tabledata;
    const totalPln = tabledata.chart["Total_pln"];
    
    dataEstimation = totalGenset + totalPln;
} catch(err) {
    console.error(err);
}

const monthKeys = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Des"];
const series = [
    { name: "Tagihan PLN", data: monthKeys.map(item => tabledata.chart[item]) },
    { name: "BBM Genset", data: monthKeys.map(item => degtabledata.chart[item]) }
];

const chartOptions = {
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
        }
    },
    dataLabels: { enabled: false },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: { categories: monthKeys },
    yaxis: {
        title: { text: 'Rp.' }
    },
    fill: {
        opacity: 1,
        colors: [colors.primary, colors.secondary],
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'vertical',
            shadeIntensity: 0.4,
            inverseColors: false,
            opacityFrom: 0.9,
            opacityTo: 0.8,
            stops: [0, 100]
        }
    },  
    colors: [colors.primary, colors.secondary],
    tooltip: {
        y: {
            formatter: function (val) {
                return "Rp." + val + " ,00"
            }
        }
    }
};
</script>
<template>
    <div class="col-xl-12 box-col-6 des-xl-50">
        <div class="card trasaction-sec">
            <div class="card-header">
                <div class="header-top d-sm-flex align-items-center">
                    <h5>Energy Cost Estimation Chart</h5>
                    <div class="center-content">
                        <p>5878 Suceessfull Transaction</p>
                    </div>
                    <div class="setting-list">
                        <ul class="list-unstyled setting-option">
                            <li>
                                <div class="setting-secondary">
                                    <VueFeather type="settings" />
                                </div>
                            </li>
                            <li><i class="view-html fa fa-code font-secondary"></i></li>
                            <li><i class="icofont icofont-maximize full-card font-secondary"></i></li>
                            <li><i class="icofont icofont-minus minimize-card font-secondary"></i></li>
                            <li><i class="icofont icofont-refresh reload-card font-secondary"></i></li>
                            <li><i class="icofont icofont-error close-card font-secondary"></i></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="transaction-totalbal">
                <h2> Rp {{ dataEstimation }} <span class="ms-3">
                    <a class="btn-arrow arrow-secondary" href="#">
                        <i class="toprightarrow-secondary fa fa-arrow-up me-2"></i>98.54%
                    </a></span>
                </h2>
                <p>Cost Tagihan PLN & BBM Genset Tahun 2023</p>
            </div>
            <div class="card-body">
                <div id="chart-widget4">
                    <VueApexCharts height="350px" type="bar" :options="chartOptions" :series="series" />
                </div>
            </div>
        </div>
    </div>
</template>