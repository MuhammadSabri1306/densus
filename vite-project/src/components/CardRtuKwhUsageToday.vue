<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@/stores/monitoring-rtu";
import { toNumberText } from "@/helpers/number-format";
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

const kwhTotalText = computed(() => {
    const kwh = monitoringRtuStore.kwhSummary?.curr_day;
    return kwh?.kwh_total ? toNumberText(kwh.kwh_total) : "--";
});

const isSaving = computed(() => {
    const savingPercent = monitoringRtuStore.kwhSaving?.daily_percent;
    if(savingPercent === null || savingPercent === 0) return null;
    return savingPercent < 0;
});

const savingPercentText = computed(() => {
    const savingPercent = monitoringRtuStore.kwhSaving?.daily_percent;
    return savingPercent === null ? "" : toNumberText(savingPercent);
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="10rem" class="mb-4" />
    <div v-else class="card income-card card-primary">                                 
        <div class="card-body text-center">                                  
            <div class="round-box">
                <VueFeather type="zap" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
            </div>
            <div class="d-flex flex-wrap align-items-start justify-content-center text-center mb-2">
                <h5 class="d-inline mb-0">{{ kwhTotalText }}</h5>
                <small v-if="kwhTotalText != '--'"><strong>&nbsp;KwH</strong></small>
            </div>
            <p>KwH Usage Hari ini</p>
            <a v-if="isSaving === true" class="btn-arrow text-danger" href="#"><i class="me-2"><VueFeather type="arrow-up-right" strokeWidth="4" style="width:0.8rem" class="ms-1 mt-1" /></i>5.54% </a>
            <a v-else-if="isSaving === false" class="btn-arrow" href="#"><i class="me-2"><VueFeather type="arrow-down-right" strokeWidth="4" style="width:0.8rem" class="ms-1 mt-1" /></i>5.54% </a>
            <div class="parrten">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 448.057 448.057" style="enable-background:new 0 0 448.057 448.057;" xml:space="preserve">
                    <g><g><path d="M404.562,7.468c-0.021-0.017-0.041-0.034-0.062-0.051c-13.577-11.314-33.755-9.479-45.069,4.099 c-0.017,0.02-0.034,0.041-0.051,0.062l-135.36,162.56L88.66,11.577C77.35-2.031,57.149-3.894,43.54,7.417                                            c-13.608,11.311-15.471,31.512-4.16,45.12l129.6,155.52h-40.96c-17.673,0-32,14.327-32,32s14.327,32,32,32h64v144                                            c0,17.673,14.327,32,32,32c17.673,0,32-14.327,32-32v-180.48l152.64-183.04C419.974,38.96,418.139,18.782,404.562,7.468z"></path></g></g><g><g><path d="M320.02,208.057h-16c-17.673,0-32,14.327-32,32s14.327,32,32,32h16c17.673,0,32-14.327,32-32 S337.694,208.057,320.02,208.057z"></path></g></g>
                </svg>
            </div>
        </div>
    </div>
</template>