<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import ListboxRegWitel from "@components/ListboxRegWitel.vue";
import GridCardRtu from "@components/GridCardRtu.vue";

const filters = reactive({
    divre: null,
    witel: null
});

const gridCardRtu = ref(null);
const onFilterSubmit = () => {
    gridCardRtu.value.fetchRtu(filters.divre, filters.witel);
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Daftar RTU</h3>
                        <DashboardBreadcrumb :items="['Monitoring RTU', 'Daftar RTU']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="card-title">Filter</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="onFilterSubmit">
                                <ListboxRegWitel @divreChange="val => filters.divre = val.divreCode" @witelChange="val => filters.witel = val.witelCode" />
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" :disabled="!filters.divre || !filters.witel" class="btn btn-primary btn-lg">Cari</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <GridCardRtu ref="gridCardRtu" />
    </div>
</template>