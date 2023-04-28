<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { toFixedNumber } from "@helpers/number-format";
import CardModern from "@components/CardModern.vue";
import Skeleton from "primevue/skeleton";
import ChartActivityStatusCount from "@components/ChartActivityStatusCount.vue";

const activityStore = useActivityStore();
const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const consistencyPercent = computed(() => {
    const chart = activityStore.chart;
    if(chart && chart.consistencyPercent !== undefined && chart.consistencyPercent !== null)
        return toFixedNumber(chart.consistencyPercent, 2);
    return null;
});

const totalActivity = computed(() => {
    const chart = activityStore.chart;
    if(!chart || !chart.statusCount)
        return null;
    const { approved, rejected, submitted } = chart.statusCount;
    return Number(approved) + Number(rejected) + Number(submitted);
});

const unapprovedTotal = computed(() => {
    const chart = activityStore.chart;
    if(!chart || !chart.statusCount)
        return null;
    const { rejected, submitted } = chart.statusCount;
    return Number(rejected) + Number(submitted);
});
</script>
<template>
    <div>
        <div v-if="!isLoading" class="row align-items-end">
            <div class="col-md-6 col-lg-4">
                <CardModern class="border-0" cardClass="bg-primary" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>% Konsistensi</b><br>
                            <span>Tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ consistencyPercent }}%</h4>
                        <VueFeather type="trending-up" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern class="border-0" cardClass="bg-secondary" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>Total Activity</b><br>
                            <span>diagendakan tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ totalActivity }} Activity</h4>
                        <VueFeather type="filter" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern class="border-0" cardClass="bg-danger" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>Unapproved Activity</b><br>
                            <span>per tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ unapprovedTotal }} Activity</h4>
                        <VueFeather type="filter" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
            </div>
            <div class="col-md-6 col-lg-8">
                <div id="chartStatusCount">
                    <ChartActivityStatusCount />
                </div>
            </div>
        </div>
        <div v-else class="p-4">
            <div class="row">
                <div v-for="n in 3" class="col-md-4">
                    <Skeleton width="100%" height="8rem" />
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>

:deep(.flicking-viewport) {
    padding: 1.5rem 3.5rem 0 3.5rem;
}

.btn-flicker-nav {
    opacity: 0.6;
}

.btn-flicker-nav:hover {
    opacity: 0.8;
}

#chartStatusCount :deep(.chart-wrapper) {
    width: 28rem;
}

</style>