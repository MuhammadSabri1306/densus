<script setup>
import { ref, computed } from "vue";
import { useViewStore } from "@stores/view";
import { usePiLatenStore } from "@stores/pi-laten";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatableGepeePiLatenV2 from "@components/DatatableGepeePiLatenV2.vue";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

const viewStore = useViewStore();
if(!viewStore.filters.month) {
    const currDate = new Date();
    viewStore.setFilter({
        month: currDate.getMonth() + 1,
        year: currDate.getFullYear()
    });
}

// gepee || amc
const piLatenStore = usePiLatenStore();
const locMode = computed(() => piLatenStore.locationMode);
const changeLocMode = mode => {
    piLatenStore.changeLocationMode(mode);
    fetchData();
};

const datatable = ref(null);
const fetchData = () => datatable.value.fetch();

const filterAutoApply = appliedFilter => appliedFilter.year && appliedFilter.month ? true : false;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const showDialogExport = ref(false);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="activity" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">PI Laten GEPEE</span>
                        </h3>
                        <DashboardBreadcrumb :items="['GEPEE Performance', 'PI Laten GEPEE']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useMonth requireMonth @apply="onFilterApply" :autoApply="filterAutoApply">
                <template #action>
                    <div class="px-md-4">
                        <div class="d-flex">
                            <label id="labelLocationMode" class="text-center mx-auto">Mode Lokasi</label>
                        </div>
                        <div class="btn-group tw-grid tw-grid-cols-2" role="group" aria-labelledby="labelLocationMode">
                            <button type="button" @click="changeLocMode('gepee')"
                                :class="(locMode == 'gepee') ? 'btn-primary' : 'btn-light'"
                                class="btn">GePEE</button>
                            <button type="button" @click="changeLocMode('amc')"
                                :class="(locMode == 'amc') ? 'btn-primary' : 'btn-light'"
                                class="btn">Semua</button>
                        </div>
                    </div>
                </template>
            </FilterGepeeV2>
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <div class="py-4">
                <div class="row g-4 justify-content-end align-items-center">
                    <div class="col-auto">
                        <button type="button" @click="showDialogExport = true" class="btn btn-outline-info bg-white btn-icon px-3">
                            <VueFeather type="download" size="1em" />
                            <span class="ms-2">Export</span>
                        </button>
                    </div>
                </div>
            </div>
            <DatatableGepeePiLatenV2 ref="datatable" />
        </div>
        <DialogExportLinkVue v-if="showDialogExport && locMode == 'gepee'" baseUrl="/export/excel/pi-laten/gepee"
            title="Export PI Laten (Mode Lokasi GePEE)" useDivre useWitel useYear useMonth requireMonth
            @close="showDialogExport = false" />
        <DialogExportLinkVue v-if="showDialogExport && locMode == 'amc'" baseUrl="/export/excel/pi-laten/amc"
            title="Export PI Laten (Mode Semua Lokasi)" useDivre useWitel useYear useMonth requireMonth
            @close="showDialogExport = false" />
    </div>
</template>