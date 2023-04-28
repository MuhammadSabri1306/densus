<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import VueApexCharts from "vue3-apexcharts";
import { baseUrl } from "@/configs/base";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const dataSavingPercent = await monitoringStore.getSavingPercent(props.rtuCode);

const savingMonthly = dataSavingPercent.savingmonthly || 0;
const savingMonthlyPercent = dataSavingPercent.savingmonthly_percent || 0;

const savingYearly = dataSavingPercent.savingyearly || 0;
const savingYearlyPercent = dataSavingPercent.savingyearly_percent || 0;

const savingDaily = dataSavingPercent.savingdaily || 0;
const savingDailyPercent = dataSavingPercent.savingdaily_percent || 0;

const series = [savingMonthlyPercent];

const chartOptions = {
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 15,
                size: '70%',
                image: (savingMonthlyPercent > 10) ? baseUrl + "assets/img/success.png" : baseUrl + "assets/img/alert.png",
                imageWidth: 64,
                imageHeight: 64,
                imageClipped: false
            },
            dataLabels: {
                name: {
                    show: false,
                    color: '#fff'
                },
                value: {
                    show: true,
                    color: savingMonthlyPercent > 10 ? "#24695c" : "#D9534F",
                    offsetY: 70,
                    fontSize: '22px',
                    fontWeight: 'bold',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                }
            }
        }
    },
    fill: { colors: "#24695c" },
    stroke: { lineCap: "round" },
    labels: ["Volatility"]
};
</script>
<template>
    <div class="card o-hidden">
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
                            <span class="font-primary">{{ savingYearlyPercent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Year</span>
                            <h5 class="num m-0"><span class="counter color-bottom">{{ savingYearly }}</span>Kwh</h5>
                        </div>
                    </div>
                    <div class="col-4 b-r-light">
                        <div>
                            <span class="font-primary">{{ savingMonthlyPercent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Month</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ savingMonthly }}</span>Kwh</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div>
                            <span class="font-primary">{{ savingDailyPercent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Today</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ savingDaily }}</span>Kwh</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>