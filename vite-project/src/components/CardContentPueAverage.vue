<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@helpers/number-format";
import { getPueTextClass } from "@helpers/pue-color";

const props = defineProps({
    value: { type: Object, required: true }
});

const datetime = computed(() => {
    if(!props.value.timestamp)
        return null;
        
    const dateObj = new Date(props.value.timestamp);
    const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
    const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    return { date, time };
});

const currMonth = computed(() => props.value.currMonth ? toFixedNumber(props.value.currMonth, 2) : 0);
const currWeek = computed(() => props.value.currWeek ? toFixedNumber(props.value.currWeek, 2) : 0);
const currDay = computed(() => props.value.currDay ? toFixedNumber(props.value.currDay, 2) : 0);
const currHour = computed(() => props.value.currHour ? toFixedNumber(props.value.currHour, 2) : 0);
</script>
<template>
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Rata-rata nilai PUE</h5>
            <p v-if="datetime" class="mb-0">Data {{ datetime.date }} pukul {{ datetime.time }}</p>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-md-3 mb-4">
                    <p class="text-center mb-0 small">Jam ini</p>
                    <h5 :class="getPueTextClass(currHour)" class="num m-0 text-center f-w-700">{{ currHour }}</h5>
                </div>
                <div class="col-6 col-md-3 mb-4 border-">
                    <p class="text-center mb-0 small">Hari ini</p>
                    <h5 :class="getPueTextClass(currDay)" class="num m-0 text-center f-w-700">{{ currDay }}</h5>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <p class="text-center mb-0 small">Minggu ini</p>
                    <h5 :class="getPueTextClass(currWeek)" class="num m-0 text-center f-w-700">{{ currWeek }}</h5>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <p class="text-center mb-0 small">Bulan ini</p>
                    <h5 :class="getPueTextClass(currMonth)" class="num m-0 text-center f-w-700">{{ currMonth }}</h5>
                </div>
            </div>
        </div>
    </div>
</template>