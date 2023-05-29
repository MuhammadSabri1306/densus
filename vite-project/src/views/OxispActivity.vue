<script setup>
import { computed, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatableOxispInput from "@components/DatatableOxispInput.vue";
import DialogOxisp from "@components/DialogOxisp.vue";
import DialogOxispAdmin from "@components/DialogOxispAdmin.vue";
import DialogOxispLocationSelect from "@components/DialogOxispLocationSelect.vue";
import DialogOxispLocationDetail from "@components/DialogOxispLocationDetail.vue";

const userStore = useUserStore();
const isAdmin = computed(() => userStore.role == "admin");

const showDialogDetail = ref(false);
const showDialogDetailAdmin = ref(false);
const showDialogLocDetail = ref(false);
const showDialogSelect = ref(false);

const route = useRoute();

const watcherSrc = () => {
    const year = route.params.year;
    const month = route.params.month;
    const idLocation = route.params.idLocation;
    return { year, month, idLocation };
};

const watcherCall = ({ year, month, idLocation }) => {
    if(!isAdmin.value && year && month && idLocation)
        showDialogDetail.value = true;
    else showDialogDetail.value = false;

    if(isAdmin.value && year && month && idLocation)
        showDialogDetailAdmin.value = true;
    else showDialogDetailAdmin.value = false;

    if(!year && !month && idLocation)
        showDialogLocDetail.value = true;
    else showDialogLocDetail.value = false;
};

watch(watcherSrc, watcherCall);
watcherCall(watcherSrc());

const viewStore = useViewStore();
if(!viewStore.filters.month) {
    const date = new Date();
    if(route.params.year)
        date.setFullYear(route.params.year);
    if(route.params.month)
        date.setMonth(route.params.month - 1);
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

const getOxispUrl = idLocation => {
    let year = viewStore.filters.year;
    let month = viewStore.filters.month;

    const currDate = new Date();
    if(!year) year = currDate.getFullYear();
    if(!month) month = currDate.getMonth() + 1;

    return `/oxisp/activity/${ year }/${ month }/${ idLocation }`;
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
        <div v-if="!isAdmin" class="container-fluid py-4">
            <div class="d-flex justify-content-end">
                <button type="button" @click="showDialogSelect = true"
                    class="btn btn-outline-info bg-white btn-icon px-3">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="middle ms-2">Data Baru</span>
                </button>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DatatableOxispInput ref="datatable" />
        </div>
        <DialogOxisp v-if="showDialogDetail" />
        <DialogOxispAdmin v-if="showDialogDetailAdmin" />
        <DialogOxispLocationSelect v-if="showDialogSelect" @close="showDialogSelect = false"
            @select="idLoc => $router.push(getOxispUrl(idLoc))"/>
        <DialogOxispLocationDetail v-if="showDialogLocDetail" @update="fetchData" />
    </div>
</template>