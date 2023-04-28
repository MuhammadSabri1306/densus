<script setup>
const props = defineProps({
    checkedCount: { type: Number, required: true },
    targetCount: { type: Number, required: true }
});

const maxCount = Math.max(props.checkedCount, props.targetCount);
</script>
<template>
    <div class="d-flex align-items-center tw-gap-x-1">
        <div v-for="item in maxCount" :class="{ 'checked': item<=checkedCount }"
            class="circle-fill-check"></div>
    </div>
</template>
<style scoped>

.circle-fill-check {
    @apply tw-w-3 tw-h-3 tw-rounded-full tw-bg-gray-400;
}

.circle-fill-check.checked {
    @apply tw-bg-[#24695c];
}

</style>