<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { toFixedNumber } from "@helpers/number-format"
import { dtColors } from "@/configs/datatable";
import VueApexCharts from "vue3-apexcharts";
import Skeleton from "primevue/skeleton";

const activityStore = useActivityStore();
const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const series = computed(() => {
    const chart = activityStore.chart;
    let data = [];
    if(chart) {
        data = chart.consistencyOnMonth.map(item => {
            if(item.percent === null)
                return 0;
            return Number(item.percent);
        });
    }

    return [{ data }];
});

const chartOptions = computed(() => {
    const chart = activityStore.chart;

    const opt = {
        chart: {
            type: "area",
            animations: { enabled: false }
        },
        xaxis: {
            type: "category"
        },
        yaxis: {
            min: 0,
            max: 100,
            tickAmount: 2
        },
        curve: "smooth",
        colors: [dtColors.primary],
        dataLabels: {
            enabled: false,
        },
        labels: {
            show: true,
            align: "right",
            minWidth: 0,
            maxWidth: 100,
            formatter: val => {
                return parseInt(val);
            },
        }
    };

    if(!chart)
        return opt;
    
    const monthList = activityStore.monthList;
    opt.xaxis.categories = chart.consistencyOnMonth.map((item, index) => monthList[index].name);
    return opt;
});
</script>
<template>
    <div class="card o-hidden">
        <div class="card-header pb-0">
            <h5>Konsistensi Activity</h5>
            <p>(%) Per Bulan</p>
        </div>
        <div class="bar-chart-widget">
            <div class="bottom-content card-body pt-0">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="w-100">
                        <Skeleton v-if="isLoading" height="141px" size="100%" />
                        <VueApexCharts width="100%" height="141px" :options="chartOptions" :series="series" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>