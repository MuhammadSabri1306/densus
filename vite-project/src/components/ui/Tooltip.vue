<script setup>

defineProps({
    tooltipClass: String,
    arrowClass: String,
    contentClass: String,
    content: String
});

</script>
<template>
    <div class="tooltip-wrapper">
        <slot></slot>
        <div :class="tooltipClass" class="tooltip">
            <div>{{ content }}</div>
            <div :class="arrowClass" class="tooltip-arrow-bottom"></div>
        </div>
    </div>
</template>
<style scoped>

.tooltip-wrapper {
    position: relative;
}

.tooltip {
    visibility: visible;
    position: absolute;
    inset: auto auto 0px 0px;
    margin: 0px;
    transform: translate(145px, 144px);
}

.tooltip-arrow-bottom {
    position: absolute;
    left: 0px;
    bottom: -4px;
    transform: translate(40px, 0px);
}

.tooltip-arrow-bottom::before {
    content: '';
    width: 10px;
    height: 10px;
    position: absolute;
    z-index: -1;
    -webkit-transform: rotate(45deg);
    -moz-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    transform: rotate(45deg);
    background: #fff;
    top: 0;
    left: 0;
}
</style>