<script setup>
import { ref } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepee from "@components/FilterGepee.vue";
import DatatableGepeeReport from "@components/DatatableGepeeReport.vue";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";

const viewStore = useViewStore();
if(!viewStore.filters.month) {
    const currDate = new Date();
    viewStore.setFilter({
        month: currDate.getMonth() + 1,
        year: currDate.getFullYear()
    });
}

const datatable = ref(null);
const fetchData = () => datatable.value.fetch();

const filterAutoApply = appliedFilter => appliedFilter.year && appliedFilter.month ? true : false;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const showCategory = ref(false);
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
            <FilterGepee requireMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <div class="py-4 d-flex justify-content-end">
                <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                    <VueFeather type="info" size="1em" />
                    <span class="ms-2">Keterangan Activity Categories</span>
                </button>
            </div>
            <DatatableGepeeReport ref="datatable" @showCategory="showCategory = true" />
        </div>
        <DialogActivityCategory v-model:visible="showCategory" />
    </div>
</template>