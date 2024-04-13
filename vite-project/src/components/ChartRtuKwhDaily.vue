<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@/stores/monitoring-rtu";
import Skeleton from "primevue/skeleton";
import { dtColors } from "@/configs/datatable";
import VueApexCharts from "vue3-apexcharts";

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

const chartOptions = {
    chart: {
        animations: { enabled: false }
    },
    xaxis: {
        type: "datetime",
        labels: { datetimeUTC: false }
    },
    title: {
        text: "Last 3 Days KwH",
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

const hasSeries = computed(() => monitoringRtuStore.kwhDailyUsages.length > 0);
const series = computed(() => {
    const kwhUsages = monitoringRtuStore.kwhDailyUsages;
    return [{
        data: kwhUsages.map(item => {
            const timestamp = getTimestamp(item.timestamp);
            const val = Number(item.kwh_value);
            return [ timestamp, val ];
        })
    }];
});
</script>
<template>
    <div class="card income-card">
        <div class="card-header pb-0">
            <h5>KwH Usage Chart</h5>
        </div>
        <div class="card-body position-relative">
            <div v-if="isLoading">
                <Skeleton width="100%" height="380px" class="mb-4" />
            </div>
            <VueApexCharts v-else width="100%" height="380px" type="area"
                :options="chartOptions" :series="series" />
            <div v-if="!isLoading && !hasSeries"
                class="position-absolute top-0 start-0 bottom-0 end-0 d-flex justify-content-center align-items-center tw-bg-neutral-100/50">
                <span class="text-center text-muted">Tidak ada data.</span>
            </div>
        </div>
    </div> 
</template>