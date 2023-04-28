<script setup>
import { ref, onMounted } from "vue";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterPln from "@components/FilterPln.vue";
import DatatablePlnParams from "@components/DatatablePlnParams.vue";
import DialogPlnParamsEdit from "@components/DialogPlnParamsEdit.vue";
import DialogPlnParamsAdd from "@components/DialogPlnParamsAdd.vue";
import DialogPlnParamsDetail from "@components/DialogPlnParamsDetail.vue";

const datatable = ref(null);
const fetchTable = () => datatable.value && datatable.value.fetch();

const showFormAdd = ref(false);
const showFormEdit = ref(false);
const showDetail = ref(false);
const currData = ref({});

const onDialogClose = () => {
    showFormEdit.value = false;
    showDetail.value = false;
    currData.value = {};
};

const openFormEdit = item => {
    currData.value = item;
    showFormEdit.value = true;
};

const openDialogDetail = item => {
    currData.value = item;
    showDetail.value = true;
};

onMounted(() => fetchTable());
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="activity" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Data Parameter PLN</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PLN', 'Parameter']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <FilterPln @submit="fetchTable()" />
        </div>
        <DatatablePlnParams ref="datatable" @create="showFormAdd = true" @edit="openFormEdit" @detail="openDialogDetail" class="container-fluid" />
        <DialogPlnParamsEdit v-if="showFormEdit" :dataParams="currData" @close="onDialogClose" @save="fetchTable()" />
        <DialogPlnParamsDetail v-if="showDetail" :dataParams="currData" @close="onDialogClose" />
        <DialogPlnParamsAdd v-if="showFormAdd" @close="showFormAdd = false" @save="fetchTable()" />
    </div>
</template>