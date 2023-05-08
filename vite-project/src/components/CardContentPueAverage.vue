<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@stores/pue-v2";
import { toFixedNumber } from "@helpers/number-format";
import { getPueTextClass } from "@helpers/pue-color";
import Skeleton from "primevue/skeleton";

const avgData = ref({});

const datetime = computed(() => {
    if(!avgData.value.timestamp)
        return null;
        
    const dateObj = new Date(avgData.value.timestamp);
    const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
    const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    return { date, time };
});

const currMonth = computed(() => avgData.value.currMonth ? toFixedNumber(avgData.value.currMonth, 2) : 0);
const currWeek = computed(() => avgData.value.currWeek ? toFixedNumber(avgData.value.currWeek, 2) : 0);
const currDay = computed(() => avgData.value.currDay ? toFixedNumber(avgData.value.currDay, 2) : 0);
const currHour = computed(() => avgData.value.currHour ? toFixedNumber(avgData.value.currHour, 2) : 0);

const pueStore = usePueV2Store();
const isLoading = ref(true);
pueStore.getAvgPue(({ data }) => {
    if(data.averages)
        avgData.value = data.averages;
    isLoading.value = false;
});
</script>
<template>
    <div>
        <Skeleton v-if="isLoading" width="100%" height="190px" class="mb-4" />
        <div v-else class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Rata-rata nilai PUE</h5>
                <p v-if="datetime" class="mb-0">Data {{ datetime.date }} pukul {{ datetime.time }}</p>
            </div>
            <div class="card-body">
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4">
                    <p class="text-center mb-0 small">Jam ini</p>
                    <p class="text-center mb-0 small">Hari ini</p>
                    <p class="d-none d-md-block text-center mb-0 small">Minggu ini</p>
                    <p class="d-none d-md-block text-center mb-0 small">Bulan ini</p>
                    <h5 :class="getPueTextClass(currHour)" class="num m-0 text-center f-w-700">{{ currHour }}</h5>
                    <h5 :class="getPueTextClass(currDay)" class="num m-0 text-center f-w-700">{{ currDay }}</h5>
                    <p class="d-md-none text-center mb-0 mt-4 small">Minggu ini</p>
                    <p class="d-md-none text-center mb-0 mt-4 small">Bulan ini</p>
                    <h5 :class="getPueTextClass(currWeek)" class="num m-0 text-center f-w-700">{{ currWeek }}</h5>
                    <h5 :class="getPueTextClass(currMonth)" class="num m-0 text-center f-w-700">{{ currMonth }}</h5>
                </div>
            </div>
        </div>
    </div>
</template>