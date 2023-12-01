<script setup>
import { ref, computed } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { toIdrCurrency } from "@helpers/number-format";
import { dtColors } from "@/configs/datatable";

const isResolve = ref(false);
const plnData = ref({});
const gensetData = ref({});

const totalEstimation = computed(() => {
    const pln = plnData.value;
    const genset = plnData.value;
    const total = (pln["Total_pln"] || 0) + (genset["Total_genset"] || 0);
    return toIdrCurrency(total);
});

const monthKeys = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
const series = computed(() => {
    const pln = plnData.value;
    const genset = plnData.value;
    return [
        { name: "Tagihan PLN", data: monthKeys.map(month => pln[month] ? pln[month] : 0) },
        { name: "BBM Genset", data: monthKeys.map(month => genset[month] ? genset[month] : 0) }
    ]
});

defineExpose({
    resolve: (plnSrcData, degSrcData) => {
        if(plnSrcData && plnSrcData.chart)
            plnData.value = plnSrcData.chart;
        if(degSrcData && degSrcData.chart)
            gensetData.value = degSrcData.chart;
        isResolve.value = true;
    }
});

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
    <div v-if="isResolve" class="card trasaction-sec">
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
            <h2> Rp {{ totalEstimation }} <span class="ms-3">
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
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>