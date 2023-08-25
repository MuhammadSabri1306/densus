<script setup>
import { ref } from "vue";
import { component as VueNumber } from "@coders-tm/vue-number-format";

const defaultFormat = {
    decimal: ".",
    separator: ",",
    // prefix: "$ ",
    // precision: 2,
    masked: false,
};

const props = defineProps({
    format: { type: Object, default: {} }
});

const number = ref({
    ...defaultFormat,
    ...props.format
});
</script>
<template>
    <VueNumber v-bind="number" />
</template>