<script setup>
import { ref, computed } from "vue";
import { useViewStore } from "@stores/view";
import { usePiLatenStore } from "@stores/pi-laten";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatableGepeePiLaten from "@components/DatatableGepeePiLaten.vue";

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
            <DatatableGepeePiLaten ref="datatable" />
        </div>
    </div>
</template>