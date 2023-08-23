<script setup>
import { ref } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepee from "@components/FilterGepee.vue";
import DatatableGepeeReport from "@components/DatatableGepeeReport.vue";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

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

const showDialogCategory = ref(false);
const showDialogExport = ref(false);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="feather" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Management Report</span>
                        </h3>
                        <DashboardBreadcrumb :items="['GEPEE Performance', 'Management Report']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepee requireMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <div class="py-4">
                <div class="row g-4 justify-content-end align-items-center">
                    <div class="col-auto">
                        <button type="button" @click="showDialogCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                            <VueFeather type="info" size="1em" />
                            <span class="ms-2">Keterangan Activity Categories</span>
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" @click="showDialogExport = true" class="btn btn-outline-info bg-white btn-icon px-3">
                            <VueFeather type="download" size="1em" />
                            <span class="ms-2">Export</span>
                        </button>
                    </div>
                </div>
            </div>
            <DatatableGepeeReport ref="datatable" @showCategory="showDialogCategory = true" />
        </div>
        <DialogActivityCategory v-model:visible="showDialogCategory" />
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/gepee-report" title="Export Gepee Management Report"
            useDivre useWitel useYear useMonth requireMonth @close="showDialogExport = false" />
    </div>
</template>