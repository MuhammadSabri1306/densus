<script setup>
import { ref } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { toFixedNumber } from "@/helpers/number-format";
import { getPueBgClass } from "@/helpers/pue-color";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

defineEmits(["close", "export"]);
const props = defineProps({
    rtuCode: { default: null }
});

const showDialog = ref(true);
const rtu = ref(null);
const tableData = ref([]);
const isEmpty = val => (val === undefined || val === null);

const pueStore = usePueV2Store();
const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    pueStore.getRtuPueOnline(props.rtuCode, data => {
        rtu.value = data?.level || null;
        tableData.value = !data?.pue_entries ? [] : data.pue_entries.map((item, index) => ({ no: index + 1, ...item }));
        isLoading.value = false;
    });
};

fetch();

const currDate = new Date();
const currYear = currDate.getFullYear();

const getDateTimeStr = timestamp => {
    return new Intl
        .DateTimeFormat('id', { dateStyle: 'long', timeStyle: 'short' })
        .format(new Date(timestamp));
};
</script>
<template>
    <Dialog header="Detail PUE Online" v-model:visible="showDialog" modal maximizable draggable
        class="dialog-basic" contentClass="tw-overflow-x-hidden" @afterHide="$emit('close')">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                <div class="row">
                    <div class="col-4 col-md-2 col-lg-auto xl:!tw-w-[4rem] mt-1">
                        <VueFeather type="feather" class="w-100 font-success" />
                    </div>
                    <div class="col">
                        <template v-if="isLoading" class="col">
                            <Skeleton height="1.5rem" width="8rem" class="mb-3" />
                            <Skeleton height="1rem" width="8rem" class="mb-3" />
                            <Skeleton height="1rem" width="8rem" class="mb-3" />
                        </template>
                        <template v-else>
                            <h6 class="m-b-5 font-success f-w-700">{{ rtu?.rtu_kode }}</h6>
                            <p class="mb-0">{{ rtu?.rtu_name }}</p>
                            <p class="mb-0">Tahun {{ currYear }}</p>
                        </template>
                    </div>
                    <div v-if="!isLoading && rtu?.rtu_kode" class="col-auto ms-auto mt-4 mt-md-auto">
                        <button type="button" @click="$emit('export', rtu.rtu_kode)" class="btn btn-lg btn-primary shadow-sm">
                            <VueFeather type="download" size="1.2em" class="middle" />
                            <span class="ms-1 middle">Export</span>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="isLoading">
                <Skeleton v-for="n in 4" class="block mb-3" />
            </div>
            <DataTable :value="tableData" showGridlines :paginator="true" :rows="10" dataKey="id"
                responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" sortable />
                <Column field="timestamp" header="Periode" sortable>
                    <template #body="{ data }">
                        <b>{{ getDateTimeStr(data.timestamp) }}</b>
                    </template>
                </Column>
                <Column field="value" header="Nilai PUE" sortable bodyClass="p-0 position-relative">
                    <template #body="{ data }">
                        <div :class="!isEmpty(data.value) ? getPueBgClass(data.value) : null"
                            class="px-3 py-1 position-absolute tw-inset-0 d-flex align-items-center f-w-700 text-dark">
                            {{ !isEmpty(data.value) ? toFixedNumber(data.value) : '-' }}
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </Dialog>
</template>