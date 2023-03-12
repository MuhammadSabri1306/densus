<script setup>
import { ref } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterRtu from "@components/FilterRtu.vue";
import GridCardRtu from "@components/GridCardRtu.vue";

const gridCardRtu = ref(null);

const viewStore = useViewStore();
const onFilterSubmit = () => {
    const divre = viewStore.filters.divre;
    const witel = viewStore.filters.witel;
    gridCardRtu.value.fetchRtu(divre, witel);
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Daftar RTU</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring RTU', 'Daftar RTU']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterRtu @apply="onFilterSubmit" />
        </div>
        <GridCardRtu ref="gridCardRtu" />
    </div>
</template>