<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toIdrCurrency } from "@helpers/number-format";
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
    const cost = monitoringRtuStore.costEstimation?.total_cost_curr_month;
    return !cost ? "" : `${ cost.month_sname } ${ cost.year }`;
});

const totalCostText = computed(() => {
    const cost = monitoringRtuStore.costEstimation?.total_cost_curr_month;
    return cost?.total_cost ? toIdrCurrency(cost.total_cost) : "--";
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="10rem" class="mb-4" />
    <div v-else class="card income-card card-primary">
        <div class="card-body text-center">
            <div class="round-box">
                <VueFeather type="dollar-sign" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
            </div>
            <div class="d-flex flex-wrap align-items-start justify-content-center text-center mb-2">
                <small v-if="totalCostText != '--'"><strong>Rp&nbsp;</strong></small>
                <h5 class="d-inline mb-0">{{ totalCostText }}</h5>
            </div>
            <h6>Total Energy Cost {{ periodeText }} (Listrik + BBM)</h6>
        </div>
    </div>
</template>