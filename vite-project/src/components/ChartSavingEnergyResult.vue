<script setup>
import { ref, computed } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { baseUrl } from "@/configs/base";

const isResolve = ref(false);
const saving = ref({});

const savingYearly = computed(() => {
    const savingData = saving.value;
    return {
        value: savingData.savingyearly || 0,
        percent: savingData.savingyearly_percent || 0
    };
});

const savingMonthly = computed(() => {
    const savingData = saving.value;
    return {
        value: savingData.savingmonthly || 0,
        percent: savingData.savingmonthly_percent || 0
    };
});

const savingDaily = computed(() => {
    const savingData = saving.value;
    return {
        value: savingData.savingdaily || 0,
        percent: savingData.savingdaily_percent || 0
    };
});

const series = computed(() => [savingMonthly.value.percent]);
const chartOptions = computed(() => {
    const savingMonthlyPercent = savingMonthly.value.percent;
    const hollowImg = (savingMonthlyPercent > 10) ? baseUrl + "assets/img/success.png" : baseUrl + "assets/img/alert.png";
    const labelColor = savingMonthlyPercent > 10 ? "#24695c" : "#D9534F";

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

defineExpose({
    resolve: dataSaving => {
        saving.value = dataSaving;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card o-hidden">
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
                <div class="row text-center">
                    <div class="col-4 b-r-light">
                        <div>
                            <span class="font-primary">{{ savingYearly.percent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Year</span>
                            <h5 class="num m-0"><span class="counter color-bottom">{{ savingYearly.value }}</span>Kwh</h5>
                        </div>
                    </div>
                    <div class="col-4 b-r-light">
                        <div>
                            <span class="font-primary">{{ savingMonthly.percent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Month</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ savingMonthly.value }}</span>Kwh</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div>
                            <span class="font-primary">{{ savingDaily.percent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Today</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ savingDaily.value }}</span>Kwh</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>