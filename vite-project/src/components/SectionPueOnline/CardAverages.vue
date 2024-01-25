<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@helpers/number-format";
import { getPueTextClass } from "@helpers/pue-color";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    averages: { required: true }
});

const isLoading = computed(() => props.isLoading);

const currDate = new Date();
const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(currDate);
const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(currDate);
const dateTimeStr = `${ date } pukul ${ time }`;

const isEmpty = val => (val === undefined || val === null);
const avgs = computed(() => {
    const src = props.averages;
    if(!src) return {};
    
    const results = {};
    [ "currMonth", "currWeek", "currDay", "currHour" ].forEach(key => {
        results[key] = isEmpty(src[key]) ? null : toFixedNumber(src[key], 2);
    });
    return results;
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="190px" class="mb-4" />
    <div v-else class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Rata-rata nilai PUE</h5>
            <p class="mb-0">Data {{ dateTimeStr }}</p>
        </div>
        <div class="card-body">
            <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4">
                <p class="text-center mb-0 small">Jam ini</p>
                <p class="text-center mb-0 small">Hari ini</p>
                <p class="d-none d-md-block text-center mb-0 small">Minggu ini</p>
                <p class="d-none d-md-block text-center mb-0 small">Bulan ini</p>
                <h5 :class="getPueTextClass(avgs?.currHour)" class="num m-0 text-center f-w-700">
                    {{ !isEmpty(avgs?.currHour) ? avgs.currHour : "-" }}
                </h5>
                <h5 :class="getPueTextClass(avgs?.currDay)" class="num m-0 text-center f-w-700">
                    {{ !isEmpty(avgs?.currDay) ? avgs.currDay : "-" }}
                </h5>
                <p class="d-md-none text-center mb-0 mt-4 small">Minggu ini</p>
                <p class="d-md-none text-center mb-0 mt-4 small">Bulan ini</p>
                <h5 :class="getPueTextClass(avgs?.currWeek)" class="num m-0 text-center f-w-700">
                    {{ !isEmpty(avgs?.currWeek) ? avgs.currWeek : "-" }}
                </h5>
                <h5 :class="getPueTextClass(avgs?.currMonth)" class="num m-0 text-center f-w-700">
                    {{ !isEmpty(avgs?.currMonth) ? avgs.currMonth : "-" }}
                </h5>
            </div>
        </div>
    </div>
</template>