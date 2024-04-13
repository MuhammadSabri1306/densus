<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useFuelStore } from "@/stores/fuel";
import { useViewStore } from "@/stores/view";
import { useUserStore } from "@/stores/user";
import { toFixedNumber, toIdrCurrency } from "@/helpers/number-format";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ButtonGroupAction from "@/components/ButtonGroupAction.vue";
import DialogFuelInvoiceForm from "@/components/DialogFuelInvoiceForm.vue";

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
    fuelStore.getInvoiceOnLocation(locationId.value, response => {
        data.value = response.data;
        isLoading.value = false;
    });
};

fetch();

const currInvoice = ref({});
const showForm = ref(false);

const onFormClose = () => {
    showForm.value = false;
    currInvoice.value = {};
};

const openFormEdit = rowIndex => {
    currInvoice.value = data.value[rowIndex];
    showForm.value = true;
};

const openFormNew = () => {
    currInvoice.value = {};
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
                            <span class="middle ms-3">Data Invoice</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring Fuel', 'Invoice']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div v-if="userRole == 'admin'" class="d-flex justify-content-end mb-4">
                <button type="button" @click="openFormNew" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">Input Invoice Baru</span>
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
                <Column field="harga" header="Harga Solar/liter" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.harga) }}
                    </template>
                </Column>
                <Column field="ppn" header="PPN" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.ppn, 2) }}
                    </template>
                </Column>
                <Column field="pph" header="PPH" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.pph, 2) }}
                    </template>
                </Column>
                <Column field="ppbkb" header="PPBKB" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.ppbkb, 2) }}
                    </template>
                </Column>
                <Column field="jumlah" header="Pembelian Liter BBM" :sortable="true">
                    <template #body="slotProps">
                        {{ toFixedNumber(slotProps.data.jumlah, 2) }}
                    </template>
                </Column>
                <Column field="fkm" header="Action">
                    <template #body="slotProps">
                        <ButtonGroupAction @edit="openFormEdit(slotProps.data.index)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <DialogFuelInvoiceForm v-if="showForm" :invoice="currInvoice" @close="onFormClose" @save="fetch" />
    </div>
</template>