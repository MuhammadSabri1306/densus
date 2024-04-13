<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useActivityStore } from "@/stores/activity";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import DataTableActivityDashboardV2 from "@/components/DataTableActivityDashboardV2/index.vue";
import DialogActivityDashboard from "@/components/DialogActivityDashboard.vue";
import SectionActivityDashboardCard from "@/components/SectionActivityDashboardCard.vue";
import FilterGepeeV2 from "@/components/FilterGepeeV2.vue";

const userStore = useUserStore();
const location = computed(() => {
    const userLevel = userStore.level;
    const userLocation = userStore.location;
    return userLevel == "nasional" ? "Nasional" : userLocation;
});

const activityStore = useActivityStore();
const currDay = computed(() => {
    const chart = activityStore.chart;
    if(!chart)
        return null;
    return new Intl.DateTimeFormat("id", { dateStyle: "long" }).format(new Date(chart.timestamp));
});

const isLoading = ref(true);
activityStore.fetchChart(false, () => isLoading.value = false);

const viewStore = useViewStore();
const datatable = ref(null);

const filterAutoApply = () => true;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    datatable.value.fetch();
};

const route = useRoute();
const showDialogActivity = ref(false);

const checkDialogShow = () => {
    if(route.params.scheduleId)
        showDialogActivity.value = true;
    else
        showDialogActivity.value = false;
};

checkDialogShow();
watch(() => route.params.scheduleId, () => checkDialogShow());

const showCard = ref(false);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row g-5">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="zap" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Activity</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Gepee Activity', 'Dashboard']" class="ms-4" />
                    </div>
                    <div class="col-auto ms-auto">
                        <div class="d-flex align-items-start">
                            <button class="btn btn-warning text-dark me-3" type="button" :title="location">
                                <span class="badge rounded-pill badge-light text-dark">
                                    <VueFeather type="map-pin" />
                                </span>
                                &nbsp;{{ location }}
                            </button>
                            <button class="btn btn-info" type="button" :title="location">
                                <span class="badge rounded-pill badge-light text-dark">
                                    <VueFeather type="calendar" />
                                </span>
                                &nbsp;{{ currDay }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div :class="{ 'show': showCard }" class="container-fluid card-wrapper">
            <SectionActivityDashboardCard class="mb-4" />
            <div v-if="!showCard" class="position-absolute bottom-0 start-50 translate-middle-x tw-z-[2]">
                <button type="button" @click="showCard = true" class="btn btn-primary btn-lg d-inline-flex align-items-center">
                    <VueFeather type="chevron-down" size="1.2em" />
                    <span class="ms-2">Selengkapnya</span>
                </button>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useYear requireYear useMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivityDashboardV2 ref="datatable" />
        </div>
        <DialogActivityDashboard v-if="showDialogActivity" />
    </div>
</template>
<style scoped>

.badge :deep(svg) {
    width: 1.5em;
    height: 1.5em;
}

.btn {
    white-space: nowrap;
}

.card-wrapper {
    overflow-y: hidden;
    max-height: 18rem;
    margin-bottom: 4rem;
    position: relative;
    transition: max-height 0.3s, margin-bottom 0.3s;
}

.card-wrapper.show {
    max-height: 100vh;
    margin-bottom: 0;
}

.card-wrapper::after {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgb(245 247 251 / 30%) 50%, rgb(245 247 251) 100%);
    opacity: 1;
    transition: opacity 0.3s;
}

.card-wrapper.show::after {
    opacity: 0;
}

</style>