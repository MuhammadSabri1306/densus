<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@/stores/activity";
import { dtColors } from "@/configs/datatable";
import VueApexCharts from "vue3-apexcharts";
import Skeleton from "primevue/skeleton";

const activityStore = useActivityStore();
const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const series = computed(() => {
    const chart = activityStore.chart;
    if(!chart)
        return [0];
    return [chart.consistencyPercent];
});

const chartOptions = {
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 15,
                size: "50%"
            },
            dataLabels: {
                name: {
                    show: false,
                    color: '#fff'
                },
                value: {
                    show: true,
                    color: dtColors.primary,
                    offsetY: 0,
                    fontSize: "22px",
                    fontWeight: "bold",
                    fontFamily: "Montserrat, sans-serif",
                }
            }
        }
    },
    fill: { colors: dtColors.success },
    stroke: { lineCap: "round" },
    labels: ["Konsistensi Activity (%)"]
};
</script>
<template>
    <div class="card o-hidden">
        <div class="card-header pb-0">
            <h5>Konsistensi Activity (%)</h5>
        </div>
        <div class="bar-chart-widget">
            <div class="top-content bg-primary"></div>
            <div class="bottom-content card-body">
                <div class="row">
                    <div class="col-12">
                        <div id="chart-widget5">
                            <Skeleton v-if="isLoading" shape="circle" size="350px" />
                            <VueApexCharts v-else height="350px" type="radialBar" :options="chartOptions" :series="series" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>