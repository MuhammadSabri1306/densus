<script setup>
import { computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { getPueBgClass } from "@helpers/pue-color";

const props = defineProps({
    pueValues: { type: Array, required: true }
});

const tabledata = computed(() => {
    const value = props.pueValues;
    if(!value)
        return [];
    return value.map((item, index) => {
        const no = index + 1;
        return { no, ...item };
    });
});

const getDateTime = timestamp => {
    return new Intl
        .DateTimeFormat('id', { dateStyle: 'long', timeStyle: 'short' })
        .format(new Date(timestamp));
};
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
        <Column field="pue_value" header="Nilai PUE" :sortable="true" bodyClass="p-0 position-relative">
            <template #body="slotProps">
                <div :class="getPueBgClass(slotProps.data.pue_value)" class="px-3 py-1 position-absolute tw-inset-0 d-flex align-items-center f-w-700 text-dark">
                    {{ toIdrCurrency(slotProps.data.pue_value) }}
                </div>
            </template>
        </Column>
    </DataTable>
</template>