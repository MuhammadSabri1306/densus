<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const performance = ref({});
const timestamp = ref(null);

const pueStore = usePueV2Store();
const isLoading = ref(true);
pueStore.getPerformance(({ data }) => {
    if(data.performances)
        performance.value = data.performances;
    if(data.timestamp)
        timestamp.value = new Date(data.timestamp);
    isLoading.value = false;
});

const datetime = computed(() => {
    if(!timestamp.value)
        return null;
    const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(timestamp.value);
    const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(timestamp.value);
    return { date, time };
});

const currMonth = computed(() => performance.value.currMonth ? toFixedNumber(performance.value.currMonth, 2) : 0);
const currWeek = computed(() => performance.value.currWeek ? toFixedNumber(performance.value.currWeek, 2) : 0);
const currDay = computed(() => performance.value.currDay ? toFixedNumber(performance.value.currDay, 2) : 0);

const getPerformanceColor = val => {
    if(val < 0)
        return "text-danger";
    if(val > 0)
        return "font-success";
    return "text-muted";
};
</script>
<template>
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Performansi PUE</h5>
            <div v-if="isLoading"><Skeleton width="17rem" class="mt-2" /></div>
            <p v-else-if="datetime" class="mb-0">
                Data {{ datetime.date }} pukul {{ datetime.time }}
            </p>
        </div>
        <div class="card-body">
            <div class="px-2">
                <div class="row g-4 justify-content-between">
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Hari ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(currDay)"
                            class="m-0 text-center f-w-700">{{ currDay }}%</h5>
                    </div>
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Minggu ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(currWeek)"
                            class="m-0 text-center f-w-700">{{ currWeek }}%</h5>
                    </div>
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Bulan ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(currMonth)"
                            class="m-0 text-center f-w-700">{{ currMonth }}%</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>