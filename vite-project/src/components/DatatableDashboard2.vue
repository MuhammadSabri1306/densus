<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const isResolve = ref(false);
const tableData = ref([]);

defineExpose({
    resolve: degData => {
        if(degData && Array.isArray(degData.table))
            tableData.value = degData.table;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card">
        <div class="card-header">
            <h5>Konsumsi Bahan Bakar Genset</h5><span>Berikut Historis Penggunaan Genset dan Estimasi Bahan Bakarnya di lokasi STO STO BALAI KOTA dengan harga Solar Industri beserta pajak per liternya adalah Rp.28.440</span>
        </div>
        <div class="card-body">
            <p v-if="tableData.length < 1" class="text-center text-muted">Tidak ada data.</p>
            <DataTable v-else :value="tableData" showGridlines :paginator="true" :rows="10"
                dataKey="id" responsiveLayout="scroll" class="table-sm">
                <Column header="No" :sortable="true">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
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
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>