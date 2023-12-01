<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";


const isResolve = ref(false);
const tableData = ref([]);

defineExpose({
    resolve: plnData => {
        if(plnData && Array.isArray(plnData.table))
            tableData.value = plnData.table;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card">
        <div class="card-header">
            <h5>Penggunaan KwH & Estimasi Biaya</h5><span>Berikut data KwH penggunaan Listrik di lokasi STO STO BALAI KOTA dengan perhitungan LWBP dan WBP</span>
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
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>