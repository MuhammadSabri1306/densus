<script setup>
import { ref } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatablePueOffline from "@components/DatatablePueOffline.vue";

const viewStore = useViewStore();
if(!viewStore.filters.month) {
    const currDate = new Date();
    viewStore.setFilter({
        month: currDate.getMonth() + 1,
        year: currDate.getFullYear()
    });
}

const datatable = ref(null);
const fetchData = () => {
    datatable.value.fetch();
};

const filterAutoApply = appliedFilter => appliedFilter.divre ? true : false;
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
                            <span class="middle ms-3">PUE Offline</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE', 'PUE Offline']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useMonth requireYear @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DatatablePueOffline ref="datatable" />
        </div>
    </div>
</template>