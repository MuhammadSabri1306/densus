<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toIdrCurrency } from "@helpers/number-format";
import { dtColors } from "@/configs/datatable";
import VueApexCharts from "vue3-apexcharts";
import Skeleton from "primevue/skeleton";

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

const periodeText = computed(() => {
    const cost = monitoringRtuStore.costEstimation?.total_cost_curr_year;
    return !cost?.year ? "-" : cost.year;
});

const totalCostText = computed(() => {
    const cost = monitoringRtuStore.costEstimation?.total_cost_curr_year;
    return !cost?.total_cost ? "-" : toIdrCurrency(cost.total_cost);
});

const months = computed(() => {
    const plnCost = monitoringRtuStore.plnCostMonthly;
    const bbmCost = monitoringRtuStore.bbmCostMonthly;

    const plnMonths = plnCost.map(item => ({ month: item?.month || null, month_sname: item?.month_sname || null }));
    const bbmMonths = bbmCost.map(item => ({ month: item?.month || null, month_sname: item?.month_sname || null }));
    const allMonths = [ ...plnMonths, ...bbmMonths ];
    const result = [];

    for(let i=0; i<allMonths.length; i++) {
        if(allMonths[i].month) {
            const duplicatedIndex = result.findIndex(item => item.month === allMonths[i].month);
            if(duplicatedIndex < 0)
                result.push(allMonths[i]);
        }
    }
    
    return result.sort((x, y) => x.month - y.month);
});

const series = computed(() => {
    const monthList = months.value;
    const plnCost = monitoringRtuStore.plnCostMonthly;
    const bbmCost = monitoringRtuStore.bbmCostMonthly;
    return [
        {
            name: "Tagihan PLN",
            data: monthList.map((monthItem) => {
                const index = plnCost.findIndex(item => item.month === monthItem.month);
                if(index < 0 || !plnCost[index]?.kwh_cost) return 0;
                return plnCost[index].kwh_cost;
            })
        },
        {
            name: "BBM Genset",
            data: monthList.map((monthItem) => {
                const index = bbmCost.findIndex(item => item.month === monthItem.month);
                if(index < 0 || !bbmCost[index]?.bbm_cost) return 0;
                return bbmCost[index].bbm_cost;
            })
        }
    ]
});

const chartOptions = computed(() => {
    const monthList = months.value;
    return {
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "55%",
                endingShape: "rounded"
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ["transparent"]
        },
        xaxis: { categories: monthList.map(item => item.month_sname) },
        yaxis: {
            title: { text: "Rp." },
            labels: {
                formatter: val => toIdrCurrency(val)
            }
        },
        fill: {
            opacity: 1,
            colors: [dtColors.primary, dtColors.secondary],
            type: "gradient",
            gradient: {
                shade: "light",
                type: "vertical",
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
                formatter: (val) => `Rp ${ toIdrCurrency(val) }`
            }
        }
    };
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="30rem" class="mb-4" />
    <div v-else class="card trasaction-sec">
        <div class="card-header">
            <div class="header-top d-sm-flex align-items-center">
                <h5>Energy Cost Estimation Chart</h5>
            </div>
        </div>
        <div class="transaction-totalbal">
            <h2 class="text-muted">Rp {{ totalCostText }}</h2>
            <p>Cost Tagihan PLN & BBM Genset Tahun {{ periodeText }}</p>
        </div>
        <div class="card-body mb-0">
            <div id="chart-widget4">
                <VueApexCharts height="350px" type="bar" :options="chartOptions" :series="series" />
            </div>
        </div>
    </div>
</template>