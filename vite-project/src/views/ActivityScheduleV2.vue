<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { onBeforeRouteLeave } from "vue-router";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@/components/FilterGepeeV2.vue";
import DataTableActivityScheduleV2 from "@/components/DataTableActivityScheduleV2/index.vue";

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
    if(hasScheduleChanged.value)
        return confirm(exitConfirmMessage);
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
            <FilterGepeeV2 useYear requireYear useMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivityScheduleV2 ref="datatableSchedule" />
        </div>
    </div>
</template>