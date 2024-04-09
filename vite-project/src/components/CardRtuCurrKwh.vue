<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toNumberText } from "@helpers/number-format";
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

const kwhValueText = computed(() => {
    const kwhValue = monitoringRtuStore.kwhCurrent;
    return (kwhValue === null) ? "-" : toNumberText(kwhValue);
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="battery-charging" />
                </div>
                <div class="media-body"><span class="m-0">KwH Saat ini</span>
                    <h4 class="mb-0 counter text-break">{{ kwhValueText }}</h4>
                    <VueFeather type="battery-charging" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>