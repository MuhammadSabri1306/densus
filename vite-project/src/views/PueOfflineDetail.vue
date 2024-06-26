<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { usePueV2Store } from "@/stores/pue-v2";
import { useViewStore } from "@/stores/view";
import { toNumberText, toRoman } from "@/helpers/number-format";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { FilterMatchMode } from "primevue/api";
import ButtonGroupAction from "@/components/ButtonGroupAction.vue";
import Skeleton from "primevue/skeleton";
import DialogPueOfflineAdd from "@/components/DialogPueOfflineAdd.vue";
import DialogPueOfflineEdit from "@/components/DialogPueOfflineEdit.vue";
import DialogPueOfflineDetail from "@/components/DialogPueOfflineDetail.vue";
import FilterGepeeMonth from "@/components/FilterGepeeMonth.vue";

const pueStore = usePueV2Store();
const route = useRoute();

const locationData = ref({});
const pueData = ref([]);

const dataTable = computed(() => {
    return pueData.value.map((item, index) => {
        const no = index + 1;
        const dayaEsensial = Number(item.daya_sdp_a) + Number(item.daya_sdp_b) + Number(item.daya_sdp_c);
        const ictEquip = Number(item.daya_eq_a) + Number(item.daya_eq_b) + Number(item.daya_eq_c);

        let periodeText = new Intl
            .DateTimeFormat('id', {
                dateStyle: 'long'
            })
            .format(new Date(item.created_at));
        if(item.week_number)
            periodeText = `Minggu ${ toRoman(item.week_number) } - ${ periodeText }`;

        return { no, dayaEsensial, ictEquip, periodeText, ...item };
    });
});

const tableFilter = ref({
    "global": { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const isLoading = ref(true);
const fetchData = () => {
    const idLocation = route.params.locationId;
    isLoading.value = true;

    pueStore.getOfflinePueDetail(idLocation, ({ data }) => {
        pueData.value = data.pue ?? [];
        locationData.value = data.location ?? {};
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
const deletePueItem = pueId => {
    const confirmed = confirm("Anda akan menghapus item Data PUE. Lanjutkan?");
    if(!confirmed)
        return;

    pueStore.deleteOffline(pueId, ({ success }) => {
        if(!success)
            return;
        viewStore.showToast("PUE Offline", "Berhasil menghapus item.", true);
        fetchData();
    });
};

const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    fetchData();
};

const formatNumberText = (pueItem, valueKey, strTemplate = "[val]", emptyText = "-") => {
    if(pueItem[valueKey] === null || pueItem[valueKey] === undefined)
        return emptyText;
    const valueText = toNumberText(pueItem[valueKey]);
    return strTemplate.replaceAll("[val]", valueText);
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
                            <span class="middle ms-3">PUE Offline</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE & IKE', 'PUE Offline']" class="ms-4" />
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
                        <FilterGepeeMonth required @apply="onFilterApply" class="d-flex align-items-center"
                            labelClass="text-muted me-2" fieldClass="me-4" />
                    </div>
                    <div class="col-auto ms-auto">
                        <button type="button" @click="isDialogAddShow = true" class="btn btn-outline-info btn-icon">
                            <VueFeather type="plus" />
                            <span>Input PUE Baru</span>
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
                <Column field="no" header="No" :sortable="true" />
                <Column field="created_at" header="Periode" :sortable="true">
                    <template #body="{ data }">
                        {{ data.periodeText }}
                    </template>
                </Column>
                <Column field="pue_value" header="Nilai PUE" :sortable="true" bodyClass="text-center f-w-700">
                    <template #body="{ data }">
                        {{ formatNumberText(data, "pue_value") }}
                    </template>
                </Column>
                <Column field="dayaEsensial" header="Total Daya Essential Facility" :sortable="true"
                    bodyClass="text-center">
                    <template #body="{ data }">
                        {{ formatNumberText(data, "dayaEsensial", "[val] Watt") }}
                    </template>
                </Column>
                <Column field="power_factor_sdp" :sortable="true" bodyClass="text-center"
                    headerClass="tw-whitespace-nowrap">
                    <template #header>
                        <span class="p-column-title">Power Factor Air<br>Conditioner Essential<br>(Cos Phi)</span>
                    </template>
                    <template #body="{ data }">
                        {{ formatNumberText(data, "power_factor_sdp") }}
                    </template>
                </Column>
                <Column field="ictEquip" header="Total Daya ICT Equipment" :sortable="true"
                    bodyClass="text-center">
                    <template #body="{ data }">
                        {{ formatNumberText(data, "ictEquip", "[val] Watt") }}
                    </template>
                </Column>
                <Column header="Action">
                    <template #body="slotProps">
                        <ButtonGroupAction @detail="showDialogDetail(slotProps.data)"
                            @edit="showDialogEdit(slotProps.data)" @delete="deletePueItem(slotProps.data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <DialogPueOfflineAdd v-if="isDialogAddShow" @close="isDialogAddShow = false" @save="fetchData" />
        <DialogPueOfflineEdit v-if="isDialogEditShow" :dataPue="currItem" @close="isDialogEditShow = false" @save="fetchData" />
        <DialogPueOfflineDetail v-if="isDialogDetailShow" :dataPue="currItem" @close="isDialogDetailShow = false" />
    </div>
</template>