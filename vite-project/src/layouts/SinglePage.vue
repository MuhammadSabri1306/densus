<script setup>
import Footer from "./Footer.vue";

await import("@/assets/css/theme.css");
await import("@/assets/css/responsive.css");
</script>
<template>
    <div class="viho-theme">
        <slot name="main"></slot>
        <Footer />
    </div>
</template>