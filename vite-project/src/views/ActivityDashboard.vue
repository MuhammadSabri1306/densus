<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterActivity from "@components/FilterActivity.vue";
import DataTableActivityDashboard from "@components/DataTableActivityDashboard/index.vue";
import DialogActivityDashboard from "@components/DialogActivityDashboard.vue";
import ChartActivityConsistencyPercent from "@components/ChartActivityConsistencyPercent.vue";
import ChartActivityStatusCount from "@components/ChartActivityStatusCount.vue";
import ChartActivityOnMonth from "@components/ChartActivityOnMonth.vue";
import CardActivityDashboardInfo from "@components/CardActivityDashboardInfo.vue";
import SectionActivityDashboardCard from "@components/SectionActivityDashboardCard.vue";

const datatable = ref(null);
const onFilterApply = () => datatable.value.fetch();
onMounted(() => datatable.value.fetch());

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
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <!-- <div class="row justify-content-end">
                <CardActivityDashboardInfo />
            </div> -->
            <SectionActivityDashboardCard class="mb-4" />
            <!-- <div class="row align-items-end">
                <div class="col-md-auto col-xl-6">
                    <CardActivityDashboardInfo />
                    <ChartActivityConsistencyPercent />
                </div>
                <div class="col-md col-xl-6">
                    <ChartActivityStatusCount />
                </div>
                <div class="col-12">
                    <ChartActivityOnMonth />
                </div>
            </div> -->
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterActivity @apply="onFilterApply" :requireDivre="false" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivityDashboard ref="datatable" />
        </div>
        <DialogActivityDashboard v-if="showDialogActivity" />
    </div>
</template>