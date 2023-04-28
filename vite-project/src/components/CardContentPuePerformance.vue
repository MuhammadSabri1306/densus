<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@helpers/number-format";

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

const getPerformanceColor = val => {
    return {
        "text-danger": val < 0,
        "font-success": val > 0,
        "text-muted": val === 0
    };
};
</script>
<template>
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Performansi PUE</h5>
            <p v-if="datetime" class="mb-0">Data {{ datetime.date }} pukul {{ datetime.time }}</p>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-md-4 mb-4 border-">
                    <p class="text-center mb-0 small">Hari ini</p>
                    <h5 :class="getPerformanceColor(currDay)" class="num m-0 text-center f-w-700">{{ currDay }}%</h5>
                </div>
                <div class="col-6 col-md-4 mb-4">
                    <p class="text-center mb-0 small">Minggu ini</p>
                    <h5 :class="getPerformanceColor(currWeek)" class="num m-0 text-center f-w-700">{{ currWeek }}%</h5>
                </div>
                <div class="col-6 col-md-4 mb-4">
                    <p class="text-center mb-0 small">Bulan ini</p>
                    <h5 :class="getPerformanceColor(currMonth)" class="num m-0 text-center f-w-700">{{ currMonth }}%</h5>
                </div>
            </div>
        </div>
    </div>
</template>