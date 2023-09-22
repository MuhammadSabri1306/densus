<script setup>

defineProps({ barClass: String });

</script>
<template>
    <div class="loading-wave">
        <div :class="barClass" class="loading-bar"></div>
        <div :class="barClass" class="loading-bar"></div>
        <div :class="barClass" class="loading-bar"></div>
        <div :class="barClass" class="loading-bar"></div>
    </div>
</template>
<style scoped>

@keyframes loading-wave-animation {
    0% { height: 10%; }
    50% { height: 50%; }
    100% { height: 10%; }
}

.loading-wave {
    display: flex;
    justify-content: center;
    align-items: flex-end;
}

.loading-bar {
    width: 6.66%;
    height: 10%;
    margin: 0 1.67%;
    border-radius: 5px;
    animation: loading-wave-animation 1s ease-in-out infinite;
}

.loading-bar:nth-child(2) {
    animation-delay: 0.1s;
}

.loading-bar:nth-child(3) {
    animation-delay: 0.2s;
}

.loading-bar:nth-child(4) {
    animation-delay: 0.3s;
}

</style>