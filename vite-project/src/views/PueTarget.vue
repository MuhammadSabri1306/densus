<script setup>
import { ref } from "vue";
import { usePueTargetStore } from "@stores/pue-target";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatablePueTargetTarget from "@components/DatatablePueTargetTarget.vue";
import DatatablePueTargetRange from "@components/DatatablePueTargetRange.vue";

const viewStore = useViewStore();
if(!viewStore.filters.quarter) {
    const currDate = new Date();
    const currMonth = currDate.getMonth() + 1;
    viewStore.setFilter({
        quarter: Math.ceil(currMonth / 3),
        year: currDate.getFullYear()
    });
}

const pueTargetStore = usePueTargetStore();
const fetchData = () => pueTargetStore.fetchReport();

const filterAutoApply = appliedFilter => appliedFilter.year && appliedFilter.quarter ? true : false;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="feather" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Target Pencapaian PUE</span>
                        </h3>
                        <DashboardBreadcrumb :items="['GEPEE Performance', 'Target Pencapaian PUE']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useQuarter requireQuarter @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-end">
                <RouterLink to="/gepee-performance/pue-target/target" class="btn btn-outline-info bg-white btn-icon px-3">Update Target</RouterLink>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec pb-4">
            <div class="tw-flex tw-flex-wrap tw-gap-x-8">
                <div class="w-100 tw-max-w-[50rem]">
                    <DatatablePueTargetTarget class="mb-5" />
                </div>
                <div class="w-100 tw-max-w-[50rem]">
                    <DatatablePueTargetRange class="mb-5" />
                </div>
            </div>
        </div>
    </div>
</template>