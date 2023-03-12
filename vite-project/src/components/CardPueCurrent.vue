<script setup>
import { computed } from "vue";
import { toFixedNumber } from "@helpers/number-format";
import Blobs from "@components/Blobs/index.vue";

const props = defineProps({
    value: { type: Object, required: true }
});

const pueValue = computed(() => {
    if(props.value && props.value.timestamp)
        return toFixedNumber(props.value.pue_value, 2);
    return null;
});

const datetime = computed(() => {
    let date = null;
    let time = null;
    if(props.value && props.value.timestamp) {
        const dateObj = new Date(props.value.timestamp);
        date = new Intl.DateTimeFormat('id', { dateStyle: 'long' }).format(dateObj);
        time = new Intl.DateTimeFormat('id', { timeStyle: 'short' }).format(dateObj);
    }
    return { date, time };
});
</script>
<template>
    <div class="card overflow-hidden bg-primary">
        <Blobs type="1" width="60%" height="120%" class="font-dark position-absolute tw-right-[-20%] tw-bottom-[-30%] tw-opacity-50" />
        <div class="card-body px-4 pt-4 pb-2 tw-z-[1]">
            <p class="mb-0">
                Nilai PUE saat ini<br>
                <b>{{ datetime.date }} pukul {{ datetime.time }}</b>
            </p>
            <p class="text-end f-36 mb-0">{{ pueValue }}</p>
        </div>
    </div>
</template>