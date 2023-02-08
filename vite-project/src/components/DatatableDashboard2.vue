<script setup>
import { ref } from "vue";
import http from "@/helpers/http-common";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    rtuCode: { required: true }
});
const filter = ref(null);

let degtabledata = null;
try {
    const response = await http.get("/monitoring/degtabledata/" + props.rtuCode);
    degtabledata = response.data.degtabledata.table;
} catch(err) {
    console.error(err);
}

degtabledata = degtabledata.map((item, index) => {
    const no = index + 1;
    return { no, ...item };
});
</script>
<template>
    <div class="card">
        <div class="card-header">
            <h5>Konsumsi Bahan Bakar Genset</h5><span>Berikut Historis Penggunaan Genset dan Estimasi Bahan Bakarnya di lokasi STO STO BALAI KOTA dengan harga Solar Industri beserta pajak per liternya adalah Rp.28.440</span>
        </div>
        <div class="card-body">
            <DataTable :value="degtabledata" showGridlines :paginator="true" :rows="10"
            v-model:filters="filter" dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" :sortable="true"></Column>
                <Column field="periode" header="Periode"></Column>
                <Column field="rtu_port" header="Data RTU" :sortable="true"></Column>
                <Column field="genset_on" header="Genset ON Count" :sortable="true"></Column>
                <Column field="hours" header="Jam Jalan" :sortable="true"></Column>
                <Column field="minutes" header="Menit Jalan" :sortable="true"></Column>
                <Column field="liter" header="Liter Dikonsumsi" :sortable="true"></Column>
                <Column field="bbm_cost" header="Total Biaya" :sortable="true">
                    <template #body="slotProps">
                        {{ 'Rp' + toIdrCurrency(slotProps.data.bbm_cost || 0) }}
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>