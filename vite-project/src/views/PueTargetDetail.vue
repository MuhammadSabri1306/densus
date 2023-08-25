<script setup>
import { ref, reactive } from "vue";
import { useViewStore } from "@stores/view";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import DatatablePueTargetDetail from "@components/DatatablePueTargetDetail.vue";
import DialogPueTargetAdd from "@components/DialogPueTargetAdd.vue";
import DialogPueTargetEdit from "@components/DialogPueTargetEdit.vue";

const viewStore = useViewStore();
if(!viewStore.filters.year) {
    viewStore.setFilter({
        year: new Date().getFullYear()
    });
}

const datatable = ref(null);
const fetchData = () => datatable.value.fetch();

const filterAutoApply = appliedFilter => appliedFilter.year ? true : false;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const showDialogCreate = ref(false);

const showDialogUpdate = ref(false);
const currTargetData = ref(null);

const onShowAddDialog = data => {
    currTargetData.value = data;
    showDialogCreate.value = true;
};

const onShowEditDialog = data => {
    currTargetData.value = data;
    showDialogUpdate.value = true;
};

const onFormDialogClose = () => {
    showDialogCreate.value = false;
    showDialogUpdate.value = false;
    currTargetData.value = null;
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
                            <span class="middle ms-3">Target Pencapaian PUE</span>
                        </h3>
                        <DashboardBreadcrumb :items="['GEPEE Performance', 'Target Pencapaian PUE']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 useYear requireYear @apply="onFilterApply" :autoApply="filterAutoApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DatatablePueTargetDetail ref="datatable" @create="onShowAddDialog" @edit="onShowEditDialog" />
        </div>
        <DialogPueTargetAdd v-if="showDialogCreate" :initData="currTargetData" @close="onFormDialogClose" @save="fetchData" />
        <DialogPueTargetEdit v-if="showDialogUpdate" :initData="currTargetData" @close="onFormDialogClose" @save="fetchData" />
    </div>
</template>