<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { dtColors } from "@/configs/datatable";
import VueApexCharts from "vue3-apexcharts";
import Skeleton from "primevue/skeleton";

const activityStore = useActivityStore();
const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const series = computed(() => {
    const chart = activityStore.chart;
    let result = [1];
    if(chart && chart.statusCount) {
        let { approved, rejected, submitted } = chart.statusCount;
        submitted = Number(submitted);
        approved = Number(approved);
        rejected = Number(rejected);

        if(submitted || approved || rejected)
            result = [submitted, approved, rejected];
    }

    return result;
});

const chartOptions = computed(() => {
    const chart = activityStore.chart;
    let colors = [dtColors.light];
    let labels = [];
    const legend = {
        show: false,
        position: "right",
        horizontalAlign: "center", 
        fontSize: "12px",
        fontFamily: "Montserrat, sans-serif"
    };
    const dataLabels = {
        enabled: false,
        dropShadow: {
            enabled: false,
            top: 1,
            left: 1,
            blur: 1,
            color: "#000",
            opacity: 0.45
        }
    };
    const tooltip = { enabled: false };

    if(chart && chart.statusCount) {
        const { approved, rejected, submitted } = chart.statusCount;
        const isExists = Number(approved) || Number(rejected) || Number(submitted);

        if(isExists) {
            colors = [ dtColors.dark, dtColors.info, dtColors.danger ];
            labels = ["Submitted", "Approved", "Rejected"];
            legend.show = true;
            dataLabels.enabled = true;
            tooltip.enabled = true;
        }
    }

    return {
        chart: { type: "pie", width: "100%" },
        colors, labels, legend, dataLabels, tooltip
    };
});
</script>
<template>
    <div class="card o-hidden">
        <div class="card-header pb-0">
            <h5>Status Activity</h5>
        </div>
        <div class="bar-chart-widget">
            <div class="bottom-content card-body">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="w-100">
                        <Skeleton v-if="isLoading" shape="circle" size="100%" />
                        <VueApexCharts v-else :options="chartOptions" :series="series" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>