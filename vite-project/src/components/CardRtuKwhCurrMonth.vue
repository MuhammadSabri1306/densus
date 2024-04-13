<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@/stores/monitoring-rtu";
import { toNumberText } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const monitoringRtuStore = useMonitoringRtuStore();
const emit = defineEmits(["update:loading"]);
const props = defineProps({
    loading: { required: true },
    kwh: { default: null },
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
    const kwh = monitoringRtuStore.kwhSummary?.curr_month;
    return !kwh ? "" : `${ kwh.month_sname } ${ kwh.year }`;
});

const kwhTotalText = computed(() => {
    const kwh = monitoringRtuStore.kwhSummary?.curr_month;
    return kwh?.kwh_total ? toNumberText(kwh.kwh_total) : "-";
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="cloud-lightning" />
                </div>
                <div class="media-body">
                    <span class="m-0">KwH Total {{ periodeText }}</span>
                    <h4 class="mb-0 counter text-break">{{ kwhTotalText }}</h4>
                    <VueFeather type="cloud-lightning" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>