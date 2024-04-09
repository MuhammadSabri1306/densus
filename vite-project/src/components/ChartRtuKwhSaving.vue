<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";
import VueApexCharts from "vue3-apexcharts";
import { baseUrl } from "@/configs/base";

const monitoringRtuStore = useMonitoringRtuStore();
const emit = defineEmits(["update:loading"]);
const props = defineProps({
    loading: { required: true },
});

const isLoading = computed({
    get() {
        return props.loading;
    },
    set(val) {
        emit("update:loading", val);
    }
});

const savingYearlyText = computed(() => {
    const saving = monitoringRtuStore.kwhSaving;
    const result = { value: "-", percent: "--" };
    if(saving?.yearly?.kwh)
        result.value = `${ toNumberText(saving.yearly.kwh) }`;
    if(saving?.yearly?.kwh_percent)
        result.percent = `${ toNumberText(saving.yearly.kwh_percent) }%`;
    return result;
});

const savingMonthlyText = computed(() => {
    const saving = monitoringRtuStore.kwhSaving;
    const result = { value: "-", percent: "--" };
    if(saving?.monthly?.kwh)
        result.value = `${ toNumberText(saving.monthly.kwh) }`;
    if(saving?.monthly?.kwh_percent)
        result.percent = `${ toNumberText(saving.monthly.kwh_percent) }%`;
    return result;
});

const savingDailyText = computed(() => {
    const saving = monitoringRtuStore.kwhSaving;
    const result = { value: "-", percent: "--" };
    if(saving?.daily?.kwh)
        result.value = `${ toNumberText(saving.daily.kwh) }`;
    if(saving?.daily?.kwh_percent)
        result.percent = `${ toNumberText(saving.daily.kwh_percent) }%`;
    return result;
});

const series = computed(() => {
    const saving = monitoringRtuStore.kwhSaving;
    return [ saving?.monthly?.kwh_percent || 0 ];
});

const chartOptions = computed(() => {
    const saving = monitoringRtuStore.kwhSaving;
    const percent = saving?.monthly.kwh_percent;
    const hollowImg = (percent > 10) ? baseUrl + "assets/img/success.png" : baseUrl + "assets/img/alert.png";
    const labelColor = percent > 10 ? "#24695c" : "#D9534F";

    return {
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 15,
                    size: "70%",
                    image: hollowImg,
                    imageWidth: 64,
                    imageHeight: 64,
                    imageClipped: false
                },
                dataLabels: {
                    name: { show: false, color: "#fff" },
                    value: {
                        show: true,
                        color: labelColor,
                        offsetY: 70,
                        fontSize: "22px",
                        fontWeight: "bold",
                        fontFamily: "Helvetica, Arial, sans-serif",
                    }
                }
            }
        },
        fill: { colors: "#24695c" },
        stroke: { lineCap: "round" },
        labels: ["Volatility"]
    };
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="30rem" />
    <div v-else class="card o-hidden">
        <div class="card-header pb-0">
            <h5>Energy Saving Result</h5>
        </div>
        <div class="bar-chart-widget">
            <div class="top-content bg-primary"></div>
            <div class="bottom-content card-body">
                <div class="row">
                    <div class="col-12"><div id="chart-widget5">
                        <VueApexCharts height="350px" type="radialBar" :options="chartOptions" :series="series" />
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-4 b-r-light">
                        <div class="text-center">
                            <span class="font-primary">{{ savingYearlyText.percent }}<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Year</span>
                            <div class="d-flex flex-wrap align-items-start justify-content-center text-center mb-2">
                                <h5 class="d-inline mb-0">{{ savingYearlyText.value }}</h5>
                                <small v-if="savingYearlyText.value != '-'" class="text-center"><strong>&nbsp;KwH</strong></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 b-r-light">
                        <div class="text-center">
                            <span class="font-primary">{{ savingMonthlyText.percent }}<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Month</span>
                            <div class="d-flex flex-wrap align-items-start justify-content-center text-center mb-2">
                                <h4 class="d-inline mb-0">{{ savingMonthlyText.value }}</h4>
                                <small v-if="savingMonthlyText.value != '-'" class="text-center"><strong>&nbsp;KwH</strong></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <span class="font-primary">{{ savingDailyText.percent }}<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Today</span>
                            <div class="d-flex flex-wrap align-items-start justify-content-center text-center mb-2">
                                <h4 class="d-inline mb-0">{{ savingDailyText.value }}</h4>
                                <small v-if="savingDailyText.value != '-'" class="text-center"><strong>&nbsp;KwH</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>