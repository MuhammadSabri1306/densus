<script setup>
import { ref } from "vue";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import DatatableUser from "@components/DatatableUser.vue";
import DialogUserDetail from "@components/DialogUserDetail.vue";
import DialogUserEdit from "@components/DialogUserEdit.vue";

const currUser = ref({});
const showDetailDialog = ref(false);
const showEditDialog = ref(false);

const onView = dataRow => {
    currUser.value = dataRow;
    showDetailDialog.value = true;
};

const onEdit = dataRow => {
    currUser.value = dataRow;
    showEditDialog.value = true;
};

const onDelete = dataRow => {
    console.log(dataRow);
};

const onDialogClose = () => {
    showDetailDialog.value = false;
    showEditDialog.value = false;
    currUser.value = {};
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Manajemen User</h3>
                        <DashboardBreadcrumb :items="['Manajemen User', 'List User']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="card">
                <div class="card-body">
                    <div>
                        <DatatableUser @view="onView" @edit="onEdit" @delete="onDelete" />
                    </div>
                </div>
            </div>
        </div>
        <DialogUserDetail v-model:visible="showDetailDialog" :data="currUser" @close="onDialogClose" />
        <DialogUserEdit v-model:visible="showEditDialog" :data="currUser" @die="onDialogClose" />
    </div>
</template>
<style>

.p-dialog:not(.p-dialog-maximized) .p-dialog-content {
    @apply md:tw-min-w-[40rem] tw-max-w-[90vw];
}

.p-dialog span {
    font-weight: normal;
}

</style>