<script setup>
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatableOxispInput from "@components/DatatableOxispInput.vue";
import DialogOxisp from "@components/DialogOxisp.vue";
import DialogOxispAdmin from "@components/DialogOxispAdmin.vue";
import DialogOxispLocationAdd from "@components/DialogOxispLocationAdd.vue";
import DialogOxispLocationDetail from "@components/DialogOxispLocationDetail.vue";

const route = useRoute();
const year = computed(() => route.params.year);
const month = computed(() => route.params.month);
const idLocation = computed(() => route.params.idLocation);

const userStore = useUserStore();
const isAdmin = computed(() => userStore.role == "admin");

const showDialogDetail = computed(() => {
    return (!isAdmin.value && year.value && month.value & idLocation.value) ? true : false;
});
const showDialogDetailAdmin = computed(() => {
    return (isAdmin.value && year.value && month.value & idLocation.value) ? true : false;
});
const showDialogLocDetail = computed(() => {
    if(idLocation.value && !year.value && !month.value)
        return true;
    return false;
});
const showDialogLocAdd = ref(false);

const viewStore = useViewStore();
if(!viewStore.filters.month) {
    const date = new Date();
    if(year.value)
        date.setFullYear(year.value);
    if(month.value)
        date.setMonth(month.value - 1);
    viewStore.setFilter({
        month: date.getMonth() + 1,
        year: date.getFullYear()
    });
}

const datatable = ref(null);
const fetchData = () => datatable.value.fetch();

const filterAutoApply = () => true;
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
                            <span class="middle ms-3">Input Activity</span>
                        </h3>
                        <DashboardBreadcrumb :items="['OXISP', 'Input Activity']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useMonth @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div v-if="isAdmin" class="container-fluid py-4">
            <div class="d-flex justify-content-end">
                <button type="button" @click="showDialogLocAdd = true"
                    class="btn btn-outline-info bg-white btn-icon px-3">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="middle ms-2">Lokasi Baru</span>
                </button>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DatatableOxispInput ref="datatable" />
        </div>
        <DialogOxisp v-if="showDialogDetail" />
        <DialogOxispAdmin v-if="showDialogDetailAdmin" />
        <DialogOxispLocationAdd v-if="showDialogLocAdd" @save="fetchData"
            @close="showDialogLocAdd = false" />
        <DialogOxispLocationDetail v-if="showDialogLocDetail" @update="fetchData" />
    </div>
</template>