<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const data = await monitoringStore.getDegTableData(props.rtuCode);
const degtabledata = data.table.map((item, index) => {
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
            dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" :sortable="true"></Column>
                <Column field="periode" header="Periode">
                    <template #body="slotProps">
                        <b>{{ slotProps.data.periode }}</b>
                    </template>
                </Column>
                <Column field="rtu_port" header="Data RTU" :sortable="true"></Column>
                <Column field="genset_on" header="Genset ON Count" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.genset_on) }}
                    </template>
                </Column>
                <Column field="hours" header="Jam Jalan" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.hours) }}
                    </template>
                </Column>
                <Column field="minutes" header="Menit Jalan" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.minutes) }}
                    </template>
                </Column>
                <Column field="liter" header="Liter Dikonsumsi" :sortable="true">
                    <template #body="slotProps">
                        {{ toIdrCurrency(slotProps.data.liter) }}
                    </template>
                </Column>
                <Column field="bbm_cost" header="Total Biaya" :sortable="true">
                    <template #body="slotProps">
                        <b>{{ 'Rp' + toIdrCurrency(slotProps.data.bbm_cost || 0) }}</b>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>