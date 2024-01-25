<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@helpers/number-format";
import Blobs from "@components/Blobs/index.vue";
import { getPueTextClass } from "@helpers/pue-color";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    highestPue: { required: true }
});

const isLoading = computed(() => props.isLoading);

const isEmpty = val => (val === undefined || val === null);
const pueValue = computed(() => {
    const highestPue = props.highestPue;
    if(isEmpty(highestPue?.pue_value))
        return null;
    return toFixedNumber(highestPue.pue_value, 2);
});

const dateTimeText = computed(() => {
    const highestPue = props.highestPue;
    if(!highestPue.timestamp)
        return null;

    const dateObj = new Date(highestPue.timestamp);
    const date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
    const time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    return `${ date } pukul ${ time } WIB`;
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="5rem" class="mb-4" />
    <div v-else>
        <div class="card overflow-hidden bg-light text-dark">
            <Blobs type="2" width="60%" height="120%" class="text-white position-absolute tw-right-[-20%] tw-bottom-[-30%] tw-opacity-50" />
            <div class="card-body px-4 pt-4 pb-2 tw-z-[1]">
                <p class="mb-0">
                    Nilai Rata-Rata PUE tertinggi tahun ini<br>
                    <b>{{ dateTimeText }}</b>
                </p>
                <p :class="getPueTextClass(pueValue)" class="text-end f-36 f-w-700 mb-0">
                    {{ !isEmpty(pueValue) ? pueValue : '-' }}
                </p>
            </div>
        </div>
    </div>
</template>