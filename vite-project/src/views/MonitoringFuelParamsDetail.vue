<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useFuelStore } from "@stores/fuel";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import { toFixedNumber } from "@helpers/number-format";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ButtonGroupAction from "@components/ButtonGroupAction.vue";
import DialogFuelParamsForm from "@components/DialogFuelParamsForm.vue";

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

const fuelStore = useFuelStore();
const isLoading = ref(true);
const fetch = () => {
    fuelStore.getParamsList(locationId.value, response => {
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
                            <span class="middle ms-3">Data Fuel Parameter</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring Fuel', 'Parameter']" class="ms-4" />
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
                <Column field="tarif_pel_pln" header="Port DEG 1 (Low)" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_deg1_low, 2) }}
                    </template>
                </Column>
                <Column field="lwbp_awal" header="Port DEG 1 (High)" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_deg1_high, 2) }}
                    </template>
                </Column>
                <Column field="lwbp_akhir" header="Port DEG 2 (Low)" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_deg2_low, 2) }}
                    </template>
                </Column>
                <Column field="wbp_awal" header="Port DEG 2 (High)" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_deg2_high, 2) }}
                    </template>
                </Column>
                <Column field="wbp_akhir" header="Port Status Digital Genset" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_status_genset, 2) }}
                    </template>
                </Column>
                <Column field="kvarh_awal" header="Port Status Digital PLN" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.port_status_pln, 2) }}
                    </template>
                </Column>
                <Column header="Action">
                    <template #body="slotProps">
                        <ButtonGroupAction @edit="openFormEdit(slotProps.data.index)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <DialogFuelParamsForm v-if="showForm" :dataParams="currData" @close="onFormClose" @save="fetch" />
    </div>
</template>