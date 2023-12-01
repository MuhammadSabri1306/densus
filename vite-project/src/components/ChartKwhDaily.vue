<script setup>
import { ref, computed } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { dtColors } from "@/configs/datatable";

const chartOptions = {
    chart: {
        animations: { enabled: false }
    },
    xaxis: {
        type: "datetime",
        labels: { datetimeUTC: false }
    },
    title: {
        text: "KwH Daily Last 3 Day",
        align: "left"
    },
    curve: "smooth",
    colors: [dtColors.primary],
    dataLabels: {
        enabled: false
    }
};

const getTimestamp = timestamp => {
    const date = new Date(timestamp);
    const dateInWIB = date.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });

    const utcDate = new Date(dateInWIB);
    return utcDate.getTime();
};

const series = ref([]);

const isResolve = ref(false);
const bbmCost = ref(null);
const bbmCostValue = computed(() => !bbmCost.value ? bbmCost.value : toIdrCurrency(bbmCost.value));

defineExpose({
    resolve: dataChart => {
        series.value = [{
            data: dataChart.map(item => {
                const timestamp = getTimestamp(item.timestamp);
                const val = Number(item.kwh_value);
                return [ timestamp, val ];
            })
        }];
        isResolve.value = true;
    }
});
</script>
<template>
    <div class="card income-card">
        <div class="card">
            <div class="card-header pb-0">
                <h5>KwH Usage Chart</h5>
            </div>
            <div class="card-body">
                <VueApexCharts v-if="isResolve" width="100%" height="380px" type="area"
                    :options="chartOptions" :series="series" />
                <div v-show="!isResolve">
                    <slot name="loading"></slot>
                </div>
            </div>
        </div>
    </div> 
</template>