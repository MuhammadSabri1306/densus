<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const data = await monitoringStore.getTableData(props.rtuCode);

const tabledata = data.table.map((item, index) => {
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
            dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" :sortable="true"></Column>
                <Column field="periode" header="Periode">
                    <template #body="slotProps">
                        <b>{{ slotProps.data.periode }}</b>
                    </template>
                </Column>
                <Column field="total_kwh" header="Total Nilai KwH" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.total_kwh) }}
                    </template>
                </Column>
                <Column field="total_lwbp" header="LWBP" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.total_lwbp) }}
                    </template>
                </Column>
                <Column field="total_wbp" header="WBP" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.total_wbp) }}
                    </template>
                </Column>
                <Column field="total_biaya" header="Total Tagihan" :sortable="true">
                    <template #body="slotProps">
                        <b>{{ 'Rp' + toIdrCurrency(slotProps.data.total_biaya) }}</b>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>