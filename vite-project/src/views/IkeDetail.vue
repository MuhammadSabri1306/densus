<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useIkeStore } from "@/stores/ike";
import { useViewStore } from "@/stores/view";
import { toNumberText, toRoman } from "@/helpers/number-format";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { FilterMatchMode } from "primevue/api";
import ButtonGroupAction from "@/components/ButtonGroupAction.vue";
import Skeleton from "primevue/skeleton";
import DialogIkeAdd from "@/components/DialogIkeAdd.vue";
import DialogIkeEdit from "@/components/DialogIkeEdit.vue";
import DialogPueOfflineEdit from "@/components/DialogPueOfflineEdit.vue";
import DialogPueOfflineDetail from "@/components/DialogPueOfflineDetail.vue";
import FilterGepeeMonth from "@/components/FilterGepeeMonth.vue";

const ikeStore = useIkeStore();
const route = useRoute();

const locationData = ref({});
const ikeData = ref([]);
const inputableWeeks = ref([]);

const dataTable = computed(() => {
    const monthList = ikeStore.monthList;

    return ikeData.value.map(item => {
        const createdAt = new Date(item.created_at);
        const periodeText = `${ monthList[createdAt.getMonth()]?.name } ${ createdAt.getFullYear() } Minggu ${ toRoman(item.week) }`;

        // const dateText = new Intl
        //     .DateTimeFormat('id', {
        //         dateStyle: 'long',
        //         timeStyle: 'short'
        //     })
        //     .format(createdAt);

        return { periodeText, ...item };
    });
});

const tableFilter = ref({
    "global": { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const isLoading = ref(true);
const fetchData = () => {
    const idLocation = route.params.locationId;
    isLoading.value = true;

    ikeStore.getLocationData(idLocation, ({ data }) => {
        if(data.ike)
            ikeData.value = data.ike;
        if(data.location)
            locationData.value = data.location;
        if(data.inputable_weeks)
            inputableWeeks.value = data.inputable_weeks;
        isLoading.value = false;
    });
};

fetchData();

const isDialogAddShow = ref(false);
const isDialogEditShow = ref(false);
const isDialogDetailShow = ref(false);
const currItem = ref(false);

const showDialogEdit = item => {
    currItem.value = item;
    isDialogEditShow.value = true;
};

const showDialogDetail = item => {
    currItem.value = item;
    isDialogDetailShow.value = true;
};

const viewStore = useViewStore();
const deleteIkeItem = ikeId => {
    const confirmed = confirm("Anda akan menghapus item Data IKE. Lanjutkan?");
    if(!confirmed)
        return;

    ikeStore.delete(ikeId, ({ success }) => {
        if(!success)
            return;
        viewStore.showToast("Data IKE", "Berhasil menghapus item.", true);
        fetchData();
    });
};

const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const formatNumber = (value, fallbackText = "-", patternText = "[value]") => {
    if(value === undefined || value === null)
        return fallbackText;
    value = toNumberText(value);
    return patternText.replaceAll("[value]", value);
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
                            <span class="middle ms-3">Monitoring IKE</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE & IKE', 'IKE']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="card card-body">
                <div v-if="isLoading" class="row g-4">
                    <div v-for="n in 12" class="col-2 col-md-3">
                        <Skeleton />
                    </div>
                </div>
                <div v-else>
                    <table class="table">
                        <tr>
                            <td class="tw-w-[1px]">Regional</td>
                            <td>: {{ locationData?.divre_name }}</td>
                        </tr>
                        <tr>
                            <td>Witel</td>
                            <td>: {{ locationData?.witel_name }}</td>
                        </tr>
                        <tr>
                            <td>Lokasi</td>
                            <td>: {{ locationData?.sto_name }}</td>
                        </tr>
                    </table>
                </div>
                <div v-if="!isLoading" class="row align-items-center g-4 mt-2">
                    <div class="col-md-auto">
                        <FilterGepeeMonth @apply="onFilterApply" class="d-flex align-items-center"
                            labelClass="text-muted me-2" fieldClass="me-4" />
                    </div>
                    <div v-if="inputableWeeks.length > 0" class="col-auto ms-auto">
                        <button type="button" @click="isDialogAddShow = true"
                            class="btn btn-outline-info btn-icon">
                            <VueFeather type="plus" />
                            <span>Input IKE Baru</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec pb-5">
            <div v-if="isLoading" class="card card-body">
                <div class="row g-4">
                    <div v-for="n in 12" class="col-2 col-md-3">
                        <Skeleton />
                    </div>
                </div>
            </div>
            <div v-else-if="dataTable.length < 1" class="card card-body p-4">
                <h4 class="text-muted text-center">Belum ada data.</h4>
            </div>
            <DataTable v-else :value="dataTable" showGridlines :paginator="true" :rows="10"
                v-model:filters="tableFilter" dataKey="id" responsiveLayout="scroll" stateStorage="session"
                stateKey="dt-state-pue-offline" class="table-sm">
                <Column field="created_at" header="No" :sortable="true">
                    <template #body="{ index }">
                        {{ (index + 1) }}
                    </template>
                </Column>
                <Column field="created_at" header="Periode" :sortable="true" bodyClass="tw-whitespace-nowrap">
                    <template #body="{ data }">
                        {{ data.periodeText }}
                    </template>
                </Column>
                <Column field="ike_value" :sortable="true" bodyClass="text-center">
                    <template #header>
                        <span>Nilai IKE <i class="fw-700">(kWh/m<sup>2</sup>)</i></span>
                    </template>
                    <template #body="{ data }">
                        {{ formatNumber(data.ike_value) }}
                    </template>
                </Column>
                <Column field="kwh_usage" :sortable="true" bodyClass="text-center">
                    <template #header>
                        <span>Penggunaan Energi <i class="fw-700">(kWh)</i></span>
                    </template>
                    <template #body="{ data }">
                        {{ formatNumber(data.kwh_usage) }}
                    </template>
                </Column>
                <Column field="area_value" :sortable="true" bodyClass="text-center">
                    <template #header>
                        <span>Luas Bangunan <i class="fw-700">(m<sup>2</sup>)</i></span>
                    </template>
                    <template #body="{ data }">
                        {{ formatNumber(data.area_value) }}
                    </template>
                </Column>
                <Column header="Action">
                    <template #body="{ data }">
                        <ButtonGroupAction :useBtnDetail="false" @edit="showDialogEdit(data)" @delete="deleteIkeItem(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <DialogIkeAdd v-if="isDialogAddShow" :location="locationData" :allowWeeks="inputableWeeks" @close="isDialogAddShow = false" @save="fetchData" />
        <DialogIkeEdit v-if="isDialogEditShow" :location="locationData" :ike="currItem" @close="isDialogEditShow = false" @save="fetchData" />
    </div>
</template>