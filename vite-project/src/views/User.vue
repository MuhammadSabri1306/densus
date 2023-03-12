<script setup>
import { ref } from "vue";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import DatatableUser from "@components/DatatableUser.vue";
import DialogUserDetail from "@components/DialogUserDetail.vue";
import DialogUserEdit from "@components/DialogUserEdit.vue";
import DialogUserAdd from "@components/DialogUserAdd.vue";

const currUser = ref({});
const showDetailDialog = ref(false);
const showEditDialog = ref(false);
const showAddDialog = ref(false);

const onView = dataRow => {
    currUser.value = dataRow;
    showDetailDialog.value = true;
};

const onEdit = dataRow => {
    currUser.value = dataRow;
    showEditDialog.value = true;
};

const onAdd = () => showAddDialog.value = true;

const onDialogClose = () => {
    showDetailDialog.value = false;
    showEditDialog.value = false;
    showAddDialog.value = false;
    currUser.value = {};
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="users" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Manajemen User</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Manajemen User', 'List User']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="card">
                <div class="card-body">
                    <Suspense>
                        <DatatableUser @view="onView" @edit="onEdit" @add="onAdd" />
                        <template #fallback>
                            <div>
                                <div class="row mb-2">
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                </div>
                                <div class="row">
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                    <div class="col"><Skeleton /></div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
            </div>
        </div>
        <DialogUserDetail v-model:visible="showDetailDialog" :data="currUser" @close="onDialogClose" />
        <DialogUserEdit v-if="showEditDialog" :data="currUser" @die="onDialogClose" />
        <DialogUserAdd v-if="showAddDialog" @die="onDialogClose" />
    </div>
</template>
<style>

.p-dialog:not(.p-dialog-maximized) .p-dialog-content {
    @apply md:tw-min-w-[40rem] tw-max-w-[90vw];
}

.p-dialog span:not(.f-w-700) {
    font-weight: normal;
}

</style>