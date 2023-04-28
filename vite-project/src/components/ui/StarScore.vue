<script setup>
import { computed } from "vue";
import { StarIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    score: { type: Number, default: 0 }
});

const scoreText = computed(() => `${ props.score }%`);
</script>
<template>
    <div class="d-flex justify-content-center align-items-center">
        <StarIcon class="tw-text-yellow-500" />
        <span>{{ scoreText }}</span>
    </div>
</template>
<style scoped>

:deep(svg) {
    width: 1.4em;
    height: 1.4em;
}

span {
    font-weight: inherit;
    margin-top: 0.2em;
    margin-left: 0.1em;
}

</style>