<script setup>
import { ref } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatableOxispChecklist from "@components/DatatableOxispChecklist.vue";
import DialogOxispChecklistCategory from "@components/DialogOxispChecklistCategory.vue";

const viewStore = useViewStore();

const datatable = ref(null);
const fetchData = () => datatable.value.fetch();

const filterAutoApply = () => true;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const showDialogCategory = ref(false);
</script>
<template>
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h3>
                        <VueFeather type="cloud-lightning" size="1.2em" class="font-primary middle" />
                        <span class="middle ms-3">OX ISP Checklist</span>
                    </h3>
                    <DashboardBreadcrumb :items="['OX ISP', 'Checklist']" class="ms-4" />
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid dashboard-default-sec">
        <FilterGepeeV2 useYear requireYear useMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
    </div>
    <div class="container-fluid py-4 d-flex justify-content-end">
        <button type="button" @click="showDialogCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
            <VueFeather type="info" size="1em" />
            <span class="ms-2">Keterangan Kategori</span>
        </button>
    </div>
    <div class="container-fluid dashboard-default-sec pb-5">
        <DatatableOxispChecklist ref="datatable" @showCategory="showDialogCategory = true" />
    </div>
    <DialogOxispChecklistCategory v-if="showDialogCategory" @close="showDialogCategory = false" />
</template>