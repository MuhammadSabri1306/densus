<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const tableData = await monitoringStore.getTableData(props.rtuCode);
let dataTotalCost;
try {
    dataTotalCost = tableData.table[0]["total_biaya"];
} catch(e) {
    dataTotalCost = 0;
}
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="dollar-sign" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Cost Listrik</span>
                    <h4 class="tw-whitespace-nowrap">Rp {{ toIdrCurrency(dataTotalCost) }}</h4>
                    <VueFeather type="dollar-sign" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>