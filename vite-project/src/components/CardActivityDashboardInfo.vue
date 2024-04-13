<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@/stores/activity";
import { useUserStore } from "@/stores/user";
import Skeleton from "primevue/skeleton";

const userStore = useUserStore();
const location = computed(() => {
    const userLevel = userStore.level;
    const userLocation = userStore.location;
    return userLevel == "nasional" ? "Nasional" : userLocation;
});

const activityStore = useActivityStore();
const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const currDay = computed(() => {
    const chart = activityStore.chart;
    if(!chart)
        return null;
    return new Intl.DateTimeFormat("id", { dateStyle: "long" }).format(new Date(chart.timestamp));
})
</script>
<template>
    <div class="card tw-min-w-[20rem] mb-0">
        <div v-if="isLoading" class="card-body">
            <Skeleton  width="100%" class="mb-4" />
            <Skeleton v-if="isLoading" width="70%" />
        </div>
        <div v-else class="card-body">
            <h4>{{ location }}</h4>
            <span>{{ currDay }}</span>
        </div>
    </div>
</template>