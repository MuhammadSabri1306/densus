<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useActivityStore } from "@stores/activity";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterActivity from "@components/FilterActivity.vue";
import DataTableActivitySchedule from "@components/DataTableActivitySchedule/index.vue";

const datatableSchedule = ref(null);
const onFilterApply = () => {
    datatableSchedule.value.fetch();
};

const activityStore = useActivityStore();
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
            <FilterActivity @apply="onFilterApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivitySchedule ref="datatableSchedule" />
        </div>
    </div>
</template>