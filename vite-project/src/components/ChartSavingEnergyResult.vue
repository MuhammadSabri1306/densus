<script setup>
import http from "@/helpers/http-common";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    stoCode: { required: true }
});

let dataSavingPercent = null;
try {
    const response = await http.get("/monitoring/savingpercent/" + props.stoCode);
    dataSavingPercent = response.data.savingpercent;
} catch(err) {
    console.error(err);
}

const series = [dataSavingPercent.savingmonthly_percent];

const chartOptions = {
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 15,
                size: '70%',
                image: dataSavingPercent.savingmonthly_percent > 10 ? "/densus/assets/img/success.png" : "/densus/assets/img/alert.png",
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
                    color: dataSavingPercent.savingmonthly_percent > 10 ? "#24695c" : "#D9534F",
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
                            <span class="font-primary">{{ dataSavingPercent.savingyearly_percent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Year</span>
                            <h5 class="num m-0"><span class="counter color-bottom">{{ dataSavingPercent.savingyearly }}</span>Kwh</h5>
                        </div>
                    </div>
                    <div class="col-4 b-r-light">
                        <div>
                            <span class="font-primary">{{ dataSavingPercent.savingmonthly_percent }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Month</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ dataSavingPercent.savingmonthly }}</span>Kwh</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div>
                            <span class="font-primary">{{ dataSavingPercent.savingdaily_percent }} }}%<i class="icon-angle-up f-12 ms-1"></i></span>
                            <span class="text-muted block-bottom">Today</span>
                            <h4 class="num m-0"><span class="counter color-bottom">{{ dataSavingPercent.savingdaily }}</span>Kwh</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>