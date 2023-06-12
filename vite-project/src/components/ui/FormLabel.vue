<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
defineProps({
    isRequired: { type: Boolean, default: false },
    useInfo: { type: Boolean, default: false }
});

const labelElm = ref(null);
const showInfo = ref(false);
const infoPost = reactive({
    x: null,
    y: null
});

const infoStyle = computed(() => {
    const x = infoPost.x || 0;
    const y = infoPost.y || 0;
    return { top: `${ y }px`, left: `${ x }px` };
});

const onInfoMouseEnter = event => {
    infoPost.x = -event.offsetX;
    infoPost.y = event.offsetY - (labelElm.value.offsetHeight - 40 || 0);
    showInfo.value = true;
};

const onInfoMouseLeave = () => {
    showInfo.value = false;
    infoPost.x = null;
    infoPost.y = null;
};
</script>
<template>
    <label ref="labelElm" for="dayaEqB">
        <span :class="{ 'required': isRequired }">
            <slot name="text"></slot>
        </span>
        <a v-if="$slots.info" role="presentation" @mouseenter="onInfoMouseEnter" @mouseleave="onInfoMouseLeave" class="label-info">
            <VueFeather ref="infoIcon" type="info" size="0.9rem" />
            <span v-if="showInfo" :style="infoStyle">
                <slot name="info"></slot>
            </span>
        </a>
    </label>
</template>
<style scoped>

a.label-info {
    @apply tw-inline tw-ml-2 tw-bg-transparent tw-cursor-pointer tw-leading-none tw-relative tw-overflow-visible;
}

a.label-info span {
    @apply tw-absolute tw-px-2 tw-py-1 tw-z-[2] tw-text-sm tw-bg-[#24695c]
        tw-text-white tw-rounded tw-w-[11rem];
}

</style>