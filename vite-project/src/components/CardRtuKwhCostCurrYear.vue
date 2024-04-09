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
    const kwh = monitoringRtuStore.costEstimation?.kwh_cost_curr_year;
    return kwh?.year || "";
});

const kwhCostText = computed(() => {
    const kwh = monitoringRtuStore.costEstimation?.kwh_cost_curr_year;
    return kwh?.kwh_cost ? toIdrCurrency(kwh.kwh_cost) : "_";
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="activity" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Cost Listrik {{ periodeText }}</span>
                    <div class="d-flex flex-wrap align-items-start mb-2">
                        <small><strong>Rp&nbsp;</strong></small>
                        <h4 class="d-inline mb-0">{{ kwhCostText }}</h4>
                    </div>
                    <VueFeather type="activity" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>