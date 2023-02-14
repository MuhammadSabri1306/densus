<script setup>
import { computed } from "vue";
import { useUserStore } from "@stores/user";

const userStore = useUserStore();
const userName = computed(() => userStore.name);
const userLocation = computed(() => userStore.location);
</script>
<template>
    <div class="sidebar-user text-center">
        <a class="setting-primary" href="#">
            <vue-feather type="settings" />
        </a>
        <img class="img-90 rounded-circle mx-auto" src="/assets/img/user1.png" alt="">
        <div class="badge-bottom">
            <span class="badge badge-primary">New</span>
        </div>
        <a href="user-profile.html">
            <h6 class="mt-3 f-14 f-w-600">{{ userName }}</h6>
        </a>
        <p class="mb-0 font-roboto text-uppercase">{{ userLocation }}</p>
    </div>
</template>