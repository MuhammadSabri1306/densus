<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { onBeforeRouteLeave } from "vue-router";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@/components/FilterGepeeV2.vue";
import DataTableActivitySchedule from "@/components/DataTableActivitySchedule/index.vue";

const viewStore = useViewStore();
const datatableSchedule = ref(null);

const activityStore = useActivityStore();
const hasScheduleChanged = computed(() => activityStore.hasScheduleChanged);
const exitConfirmMessage = "Terdapat perubahan yang belum disimpan. Batalkan perubahan dan lanjutkan?";

const onBeforeUnload = event => {
    if(hasScheduleChanged.value) {
        event.preventDefault();
        event.returnValue = exitConfirmMessage;
        return exitConfirmMessage;
    }
};

onMounted(() => {
    window.addEventListener("beforeunload", onBeforeUnload);
});

onUnmounted(() => {
    window.removeEventListener("beforeunload", onBeforeUnload);
});

const confirmExit = () => {
    if(hasScheduleChanged.value) {
        const hasConfirmed = confirm(exitConfirmMessage);
        if(hasConfirmed)
            activityStore.setHasScheduleChanged(false);
        return hasConfirmed;
    }
    return true;
};

onBeforeRouteLeave(() => {
    if(!confirmExit())
        return false;
});

const filterAutoApply = appliedFilter => appliedFilter.divre ? true : false;
const onFilterApply = filterValue => {
    if(confirmExit()) {
        viewStore.setFilter(filterValue);
        datatableSchedule.value.fetch();
    }
};

const onDatatableUpdated = () => activityStore.setHasScheduleChanged(false);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="zap" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Penjadwalan</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Gepee Activity', 'Penjadwalan']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivitySchedule ref="datatableSchedule" @update="onDatatableUpdated" />
        </div>
    </div>
</template>