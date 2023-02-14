<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const tableData = await monitoringStore.getTableData(props.rtuCode);
const degTableData = await monitoringStore.getDegTableData(props.rtuCode);

const totalPln = tableData.chart["Total_pln"] || 0;
const totalGenset = degTableData.chart["Total_genset"] || 0;
const dataEstimation = totalGenset + totalPln;

const monthKeys = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
const series = [
    { name: "Tagihan PLN", data: monthKeys.map(item => tableData.chart[item]) },
    { name: "BBM Genset", data: monthKeys.map(item => degTableData.chart[item]) }
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
        colors: [dtColors.primary, dtColors.secondary],
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
    colors: [dtColors.primary, dtColors.secondary],
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
</template>