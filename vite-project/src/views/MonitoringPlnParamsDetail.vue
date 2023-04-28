<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePlnStore } from "@stores/pln";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import { toFixedNumber } from "@helpers/number-format";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ButtonGroupAction from "@components/ButtonGroupAction.vue";
import DialogPlnParamsForm from "@components/DialogPlnParamsForm.vue";

const viewStore = useViewStore();
const data = ref([]);
const tableData = computed(() => {
    return data.value.map((item, index) => {
        const no = index + 1;
        return { index, no, ...item };
    });
});

const buildPeriodeText = tgl => {
    const date = new Date(tgl);
    const year = date.getFullYear();
    const monthName = viewStore.monthList[date.getMonth()]?.name;
    return `${ monthName } ${ year }`;
};

const route = useRoute();
const locationId = computed(() => route.params.locationId);

const plnStore = usePlnStore();
const isLoading = ref(true);
const fetch = () => {
    plnStore.getParamsList(locationId.value, response => {
        data.value = response.data;
        isLoading.value = false;
    });
};

fetch();

const currData = ref({});
const showForm = ref(false);

const onFormClose = () => {
    showForm.value = false;
    currData.value = {};
};

const openFormEdit = rowIndex => {
    currData.value = data.value[rowIndex];
    showForm.value = true;
};

const openFormNew = () => {
    currData.value = {};
    showForm.value = true;
};

const userStore = useUserStore();
const userRole = computed(() => userStore.role);
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
            <div v-if="userRole == 'admin'" class="d-flex justify-content-end mb-4">
                <button type="button" @click="openFormNew" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">Input Parameter Baru</span>
                </button>
            </div>
            <DataTable :value="tableData" showGridlines :paginator="true" :rows="10"
                dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" :sortable="true" />
                <Column field="timestamp" header="Periode" :sortable="true">
                    <template #body="slotProps">
                        <b>{{ buildPeriodeText(slotProps.data.tgl) }}</b>
                    </template>
                </Column>
                <Column field="sto_name" header="Lokasi" :sortable="true" />
                <Column field="tarif_pel_pln" header="Tarif" :sortable="true" />
                <Column field="lwbp_awal" header="LWBP Awal" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.lwbp_awal, 2) }}
                    </template>
                </Column>
                <Column field="lwbp_akhir" header="LWBP Akhir" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.lwbp_akhir, 2) }}
                    </template>
                </Column>
                <Column field="wbp_awal" header="WBP Awal" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.wbp_awal, 2) }}
                    </template>
                </Column>
                <Column field="wbp_akhir" header="WBP Akhir" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.wbp_akhir, 2) }}
                    </template>
                </Column>
                <Column field="kvarh_awal" header="kVARh Awal" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.kvarh_awal, 2) }}
                    </template>
                </Column>
                <Column field="kvarh_akhir" header="kVARh Akhir" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.kvarh_akhir, 2) }}
                    </template>
                </Column>
                <Column field="fkm" header="Faktor Kali Meter" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.kvarh_akhir, 2) }}
                    </template>
                </Column>
                <Column field="fkm" header="Action">
                    <template #body="slotProps">
                        <ButtonGroupAction @edit="openFormEdit(slotProps.data.index)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <DialogPlnParamsForm v-if="showForm" :dataParams="currData" @close="onFormClose" @save="fetch" />
    </div>
</template>