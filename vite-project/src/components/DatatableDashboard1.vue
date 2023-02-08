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
let tabledata = null;
try {
    const response = await http.get("/monitoring/tabledata/" + props.rtuCode);
    tabledata = response.data.tabledata.table;
} catch(err) {
    console.error(err);
}

tabledata = tabledata.map((item, index) => {
    const no = index + 1;
    return { no, ...item };
});
</script>
<template>
    <div class="card">
        <div class="card-header">
            <h5>Penggunaan KwH & Estimasi Biaya</h5><span>Berikut data KwH penggunaan Listrik di lokasi STO STO BALAI KOTA dengan perhitungan LWBP dan WBP</span>
        </div>
        <div class="card-body">
            <DataTable :value="tabledata" showGridlines :paginator="true" :rows="10"
            v-model:filters="filter" dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" :sortable="true"></Column>
                <Column field="periode" header="Periode"></Column>
                <Column field="total_kwh" header="Total Nilai KwH" :sortable="true"></Column>
                <Column field="total_lwbp" header="LWBP" :sortable="true"></Column>
                <Column field="total_wbp" header="WBP" :sortable="true"></Column>
                <Column field="total_biaya" header="Total Tagihan" :sortable="true">
                    <template #body="slotProps">
                        {{ 'Rp' + toIdrCurrency(slotProps.data.total_biaya) }}
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>