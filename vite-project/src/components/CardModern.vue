<script setup>
defineProps({
    feather: String,
    title: String,
    data: String,
    cardClass: String,
    contentClass: String
});
</script>
<template>
    <div class="card o-hidden">
        <div :class="cardClass" class="b-r-4 card-body">
            <div v-if="$slots.content" class="media static-top-widget">
                <div :class="contentClass" class="media-body">
                    <slot name="content"></slot>
                </div>
            </div>
            <div v-else class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather :type="feather" />
                </div>
                <div :class="contentClass" class="media-body">
                    <span class="m-0">KwH Saat ini</span>
                    <h4 class="mb-0 counter">156</h4>
                    <VueFeather :type="feather" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>