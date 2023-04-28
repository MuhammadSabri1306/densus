<script setup>
import { ref, reactive, computed } from "vue";
import { usePueV2Store } from "@stores/pue-v2";
import { toFixedNumber } from "@helpers/number-format";
import Blobs from "@components/Blobs/index.vue";
import { getPueBgClass } from "@helpers/pue-color";
import Skeleton from "primevue/skeleton";

const emit = defineEmits(["loaded"]);

const currPue = reactive({
    value: null,
    timestamp: null,
    isAvg: null
});

const pueStore = usePueV2Store();
const isLoading = ref(true);
pueStore.getLatestPue(({ data }) => {
    if(data.latestValue || data.latestValue !== 0)
        currPue.value = data.latestValue;
    if(data.timestamp)
        currPue.timestamp = data.timestamp;
    if(data.isAvg)
        currPue.isAvg = data.isAvg;
    if(data.request_location)
        emit("loaded", data.request_location);
    isLoading.value = false;
});

const pueValue = computed(() => currPue.value ? toFixedNumber(currPue.value, 2) : null);
const title = computed(() => {
    if(currPue.isAvg === null)
        return null;
    if(!currPue.isAvg)
        return "Nilai PUE saat ini";
    return "Nilai Rata-Rata PUE saat ini";
});

const dateTimeText = computed(() => {
    if(!currPue.timestamp)
        return null;

    const dateObj = new Date(currPue.timestamp);
    const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
    const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    return `${ date } pukul ${ time } WIB`;
});
</script>
<template>
    <div>
        <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
        <div v-else :class="getPueBgClass(pueValue)" class="card overflow-hidden">
            <Blobs type="1" width="60%" height="120%" class="font-dark position-absolute tw-right-[-20%] tw-bottom-[-30%] tw-opacity-50" />
            <div class="card-body px-4 pt-4 pb-2 tw-z-[1]">
                <p class="mb-0">
                    {{ title }}<br><b>{{ dateTimeText }}</b>
                </p>
                <p class="text-end f-36 f-w-700 mb-0">{{ pueValue }}</p>
            </div>
        </div>
    </div>
</template>