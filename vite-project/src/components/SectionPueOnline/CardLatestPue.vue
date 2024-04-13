<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@/helpers/number-format";
import Blobs from "@/components/Blobs/index.vue";
import { getPueBgClass } from "@/helpers/pue-color";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    latestPue: { required: true }
});

const isLoading = computed(() => props.isLoading);

const isEmpty = val => (val === undefined || val === null);
const pueValue = computed(() => {
    const latestPue = props.latestPue;
    if(isEmpty(latestPue?.pue_value))
        return null;
    return toFixedNumber(latestPue.pue_value, 2);
});

const dateTimeText = computed(() => {
    const pue = props.latestPue;
    if(!pue.timestamp)
        return null;

    const dateObj = new Date(pue.timestamp);
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
                    Nilai Rata-Rata PUE saat ini<br><b>{{ dateTimeText }}</b>
                </p>
                <p class="text-end f-36 f-w-700 mb-0">{{ !isEmpty(pueValue) ? pueValue : '-' }}</p>
            </div>
        </div>
    </div>
</template>