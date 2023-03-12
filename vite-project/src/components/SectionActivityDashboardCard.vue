<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { toIdrCurrency } from "@helpers/number-format";
import CardModern from "@components/CardModern.vue";
import Skeleton from "primevue/skeleton";
import Flicking from "@egjs/vue3-flicking";
import ChartActivityStatusCount from "@components/ChartActivityStatusCount.vue";
import ChartActivityOnMonth from "@components/ChartActivityOnMonth.vue";

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
});

const consistencyPercent = computed(() => {
    const chart = activityStore.chart;
    if(chart && chart.consistencyPercent !== undefined && chart.consistencyPercent !== null)
        return toIdrCurrency(chart.consistencyPercent, 2);
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

const flicking = ref(null);
const slidePrev = () => flicking.value && flicking.value.prev();
const slideNext = () => flicking.value && flicking.value.next();
</script>
<template>
    <div class="rounded border overflow-hidden w-100 position-relative">
        <div v-if="!isLoading" class="flicking-slide-wrapper">
            <Flicking ref="flicking" :options="{ align: 'prev', circular: true, panelsPerView: -1 }">
                <div key="1" class="card ms-4 tw-min-w-[20rem]">
                    <div class="card-body">
                        <h4>{{ location }}</h4>
                        <span>{{ currDay }}</span>
                    </div>
                </div>
                <CardModern key="2" class="border-0 ms-4 tw-min-w-[15rem]" cardClass="bg-primary" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>% Konsistensi</b><br>
                            <span>Tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ consistencyPercent }}%</h4>
                        <VueFeather type="trending-up" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern key="3" class="border-0 ms-4 tw-min-w-[20rem]" cardClass="bg-secondary" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>Total Activity</b><br>
                            <span>diagendakan tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ totalActivity }} Activity</h4>
                        <VueFeather type="filter" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern key="4" class="border-0 ms-4 tw-min-w-[20rem]" cardClass="bg-danger" contentClass="ps-0">
                    <template #content>
                        <p class="mb-0">
                            <b>Unapproved Activity</b><br>
                            <span>per tahun ini</span>
                        </p>
                        <h4 class="mb-0 text-end">{{ unapprovedTotal }} Activity</h4>
                        <VueFeather type="filter" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <ChartActivityStatusCount key="5" class="ms-4" />
                <ChartActivityOnMonth key="6" class="ms-4 tw-w-[40rem]" />
            </Flicking>
            <div class="position-absolute top-50 start-0 translate-middle-y p-2 tw-z-[2]">
                <button type="button" @click="slidePrev" class="btn-circle btn btn-primary shadow p-0 btn-flicker-nav" title="previous">
                    <VueFeather type="chevron-left" fill="#fff" size="1.2rem" />
                </button>
            </div>
            <div class="position-absolute top-50 end-0 translate-middle-y p-2 tw-z-[2]">
                <button type="button" @click="slideNext" class="btn-circle btn btn-primary shadow p-0 btn-flicker-nav" title="next">
                    <VueFeather type="chevron-right" fill="#fff" size="1.2rem" />
                </button>
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

</style>