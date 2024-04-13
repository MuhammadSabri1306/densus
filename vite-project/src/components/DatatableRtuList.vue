<script setup>
import { ref, computed } from "vue";
import { useRtuStore } from "@/stores/rtu";
import { useUserStore } from "@/stores/user";
import { FilterMatchMode } from "primevue/api";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const filter = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS}
});
const rtuStore = useRtuStore();
const tableRtu = computed(() => rtuStore.datatable);

await rtuStore.fetchList();

const userStore = useUserStore();
const userRole = computed(() => userStore.role);
</script>
<template>
    <div>
        <div class="row mb-4">
            <div class="col-6 col-md-4 ms-auto">
                <input type="search" v-model="filter['global'].value" id="rtuSearch" class="form-control form-control-lg" placeholder="Cari" aria-label="Cari" />
            </div>
        </div>
        <DataTable :value="tableRtu" showGridlines :paginator="true" :rows="10"
            v-model:filters="filter" dataKey="id" stateStorage="session"
            stateKey="dt-state-rtu" responsiveLayout="scroll" class="table-sm">
            <Column field="no" header="No" :sortable="true" />
            <Column field="rtu_kode" header="KODE RTU" :sortable="true" />
            <Column field="rtu_name" header="NAMA RTU" :sortable="true" />
            <Column field="lokasi" header="LOKASI" :sortable="true" />
            <Column field="sto_kode" header="STO" :sortable="true" />
            <Column field="witel_name" header="WITEL" :sortable="true" />
            <Column field="divre_name" header="DIVRE" :sortable="true" />
            <Column v-if="userRole == 'admin'" header="">
                <template #body="slotProps">
                    <RouterLink :to="'/rtu/detail/'+slotProps.data.id" class="btn btn-sm btn-success">Detail</RouterLink>
                </template>
            </Column>
        </DataTable>
    </div>
</template>