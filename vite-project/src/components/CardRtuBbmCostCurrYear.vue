<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toIdrCurrency } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

const monitoringRtuStore = useMonitoringRtuStore();
const emit = defineEmits(["update:loading"]);
const props = defineProps({
    loading: { required: true },
    bbm: { default: null },
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
    const bbm = monitoringRtuStore.costEstimation?.bbm_cost_curr_year;
    return bbm?.year || "";
});

const bbmCostText = computed(() => {
    const bbm = monitoringRtuStore.costEstimation?.bbm_cost_curr_year;
    return bbm?.bbm_cost ? toIdrCurrency(bbm.bbm_cost) : "_";
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="droplet" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Biaya Solar {{ periodeText }}</span>
                    <div class="d-flex flex-wrap align-items-start mb-2">
                        <small><strong>Rp&nbsp;</strong></small>
                        <h4 class="d-inline mb-0">{{ bbmCostText }}</h4>
                    </div>
                    <VueFeather type="droplet" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>