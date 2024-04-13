<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    performances: { required: true }
});

const isLoading = computed(() => props.isLoading);

const currDate = new Date();
const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(currDate);
const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(currDate);
const dateTimeStr = `${ date } pukul ${ time }`;

const isEmpty = val => (val === undefined || val === null);
const numberValueToText = (val) => {
    if(isEmpty(val))
        return "-";
    return `${ -toFixedNumber(val, 2) }%`;
};

const perfs = computed(() => {
    const src = props.performances;
    if(!src) return {};
    
    const results = {};
    [ "currMonth", "currWeek", "currDay" ].forEach(key => {
        results[key] = isEmpty(src[key]) ? null : toFixedNumber(src[key], 2);
    });
    return results;
});

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
            <p v-else class="mb-0">Data {{ dateTimeStr }}</p>
        </div>
        <div class="card-body">
            <div class="px-2">
                <div class="row g-4 justify-content-between">
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Hari ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(perfs?.currDay)" class="m-0 text-center f-w-700">
                            {{ numberValueToText(perfs?.currDay) }}
                        </h5>
                    </div>
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Minggu ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(perfs?.currWeek)" class="m-0 text-center f-w-700">
                            {{ numberValueToText(perfs?.currWeek) }}
                        </h5>
                    </div>
                    <div class="col-auto">
                        <p class="text-center mb-0 small">Bulan ini</p>
                        <div v-if="isLoading" class="d-flex justify-content-center">
                            <Skeleton width="6rem" height="1.5rem" />
                        </div>
                        <h5 v-else :class="getPerformanceColor(perfs?.currMonth)" class="m-0 text-center f-w-700">
                            {{ numberValueToText(perfs?.currMonth) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>