<script setup>
import { defineAsyncComponent } from "vue";
const props = defineProps({
    type: { type: String, required: true },
    size: String,
    width: String,
    height: String,
    fill: String
});

const blobWidth = props.size || props.width || "100%";
const blobHeight = props.size || props.width || "auto";
const blobFill = props.fill || "currentColor";

const blobStyle = {
    width: props.size || props.width || "100%",
    height: props.size || props.height || "auto",
    fill: props.fill || "currentColor"
};

const blob = defineAsyncComponent(() => import(`./blob${ props.type }.vue`));
</script>
<template>
    <component :is="blob" :style="blobStyle" />
</template>