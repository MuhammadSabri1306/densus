<script setup>
import Footer from "./Footer.vue";
import "bootstrap/dist/css/bootstrap.min.css";
import "@/assets/css/app.css";
import "@/assets/css/theme.css";
import "@/assets/css/responsive.css";
</script>
<template>
    <div class="viho-theme">
        <RouterView />
        <Footer />
    </div>
</template>