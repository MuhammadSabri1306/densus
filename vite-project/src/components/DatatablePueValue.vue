<script setup>
import { computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({
    pueValues: { type: Array, required: true }
});

const tabledata = computed(() => {
    return props.pueValues.map((item, index) => {
        const no = index + 1;
        return { no, ...item };
    });
});

const getDateTime = timestamp => new Intl.DateTimeFormat('id', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(timestamp));
</script>
<template>
    <DataTable :value="tabledata" showGridlines :paginator="true" :rows="10"
    dataKey="id" responsiveLayout="scroll" class="table-sm">
        <Column field="no" header="No" :sortable="true"></Column>
        <Column field="timestamp" header="Periode" :sortable="true">
            <template #body="slotProps">
                <b>{{ getDateTime(slotProps.data.timestamp) }}</b>
            </template>
        </Column>
        <Column field="pue_value" header="Nilai PUE" :sortable="true">
            <template #body="slotProps">
                {{ toIdrCurrency(slotProps.data.pue_value) }}
            </template>
        </Column>
    </DataTable>
</template>