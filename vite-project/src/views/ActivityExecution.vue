<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useUserStore } from "@stores/user";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterActivity from "@components/FilterActivity.vue";
import DataTableActivityExecution from "@components/DataTableActivityExecution/index.vue";
import DialogActivityExec from "@components/DialogActivityExec.vue";
import DialogActivityExecAdmin from "@components/DialogActivityExecAdmin.vue";

const datatableExecution = ref(null);
const onFilterApply = () => {
    datatableExecution.value.fetch();
};

const route = useRoute();

const userStore = useUserStore();
const userRole = computed(() => userStore.role);

const showDialogExec = ref(false);
const showDialogExecAdmin = ref(false);

const checkDialogShow = scheduleId => {
    showDialogExec.value = scheduleId && userRole.value != "admin";
    showDialogExecAdmin.value = scheduleId && userRole.value == "admin";
};

checkDialogShow(route.params.scheduleId);
watch(() => route.params.scheduleId, checkDialogShow);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="zap" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Pelaksanaan</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Gepee Activity', 'Pelaksanaan']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterActivity @apply="onFilterApply" />
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <DataTableActivityExecution ref="datatableExecution" />
        </div>
        <DialogActivityExec v-if="showDialogExec" />
        <DialogActivityExecAdmin v-if="showDialogExecAdmin" />
    </div>
</template>