<script setup>
import http from "@/helpers/http-common";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    rtuCode: { required: true }
});

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
            <DataTable :value="degtabledata">
                <Column field="no" header="No" :sortable="true"></Column>
                <Column field="periode" header="Periode" :sortable="true"></Column>
                <Column field="rtu_port" header="Data RTU" :sortable="true"></Column>
                <Column field="genset_on" header="Genset ON Count" :sortable="true"></Column>
                <Column field="hours" header="Jam Jalan" :sortable="true"></Column>
                <Column field="minutes" header="Menit Jalan" :sortable="true"></Column>
                <Column field="liter" header="Liter Dikonsumsi" :sortable="true"></Column>
                <Column field="bbm_cost" header="Total Biaya" :sortable="true"></Column>
            </DataTable>
        </div>
    </div>
</template>