<script setup>
import { computed, reactive } from "vue";
import { usePueV2Store } from "@stores/pue-v2";
import { toFixedNumber } from "@helpers/number-format";
import Blobs from "@components/Blobs/index.vue";
import { getPueTextClass } from "@helpers/pue-color";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    title: { type: String, default: "" }
});

const dataPue = reactive({
    maxValue: null,
    timestamp: null
});

const pueStore = usePueV2Store();
const isLoading = computed(() => pueStore.isLoading.chartData);

const pueValue = computed(() => {
    const pueMax = pueStore.maxValue;
    if(!pueMax)
        return null;
    return toFixedNumber(pueMax.pue_value, 2);
});

const datetime = computed(() => {
    const pueMax = pueStore.maxValue;
    let date = null;
    let time = null;
    if(!pueMax)
        return { date, time };

    const dateObj = new Date(pueMax.timestamp);
    date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
    time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    return { date, time };
});


// pueStore.
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else>
        <div class="card overflow-hidden bg-light text-dark">
            <Blobs type="2" width="60%" height="120%" class="text-white position-absolute tw-right-[-20%] tw-bottom-[-30%] tw-opacity-50" />
            <div class="card-body px-4 pt-4 pb-2 tw-z-[1]">
                <p class="mb-0">
                    {{ title }}<br><b>{{ datetime.date }} pukul {{ datetime.time }}</b>
                </p>
                <p :class="getPueTextClass(pueValue)" class="text-end f-36 f-w-700 mb-0">{{ pueValue }}</p>
            </div>
        </div>
    </div>
</template>