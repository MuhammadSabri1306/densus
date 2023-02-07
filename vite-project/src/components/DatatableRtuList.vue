<script setup>
import { ref, computed } from "vue";
// import { FilterMatchMode } from "primevue/api";
import http from "@/helpers/http-common";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const filter = ref(null);
const dataRtu = ref([]);
const tableRtu = computed(() => {
    return dataRtu.value.map((item, index) => {
        const no = index + 1;
        return { no, ...item };
    });
});

try {
    const response = await http.get("/rtu");
    if(response.data.rtu)
        dataRtu.value = response.data.rtu;
} catch(err) {
    console.error(err);
}
</script>
<template>
    <div>
        <div class="row mb-4">
            <div class="col-6 col-md-4 ms-auto">
                <input type="search" v-model="filter" id="rtuSearch" class="form-control form-control-lg" placeholder="Cari" aria-label="Cari" />
            </div>
        </div>
        <DataTable :value="tableRtu" showGridlines :paginator="true" :rows="10"
            v-model:filters="filter" selectionMode="single"
            dataKey="id" stateStorage="session" stateKey="dt-state-rtu" class="table-sm">
            <Column field="no" header="No" :sortable="true" />
            <Column field="rtu_kode" header="KODE RTU" :sortable="true" />
            <Column field="rtu_name" header="NAMA RTU" :sortable="true" />
            <Column field="lokasi" header="LOKASI" :sortable="true" />
            <Column field="sto_kode" header="STO" :sortable="true" />
            <Column field="witel_name" header="WITEL" :sortable="true" />
            <Column field="divre_name" header="DIVRE" :sortable="true" />
            <Column header="">
                <template #body="slotProps">
                    <RouterLink :to="'/rtu/detail/'+slotProps.data.id" class="btn btn-sm btn-success">Detail</RouterLink>
                </template>
            </Column>
        </DataTable>
    </div>
</template>