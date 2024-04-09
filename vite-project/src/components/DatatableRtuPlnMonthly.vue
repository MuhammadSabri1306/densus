<script setup>
import { computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toIdrCurrency, toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const monitoringRtuStore = useMonitoringRtuStore();
const emit = defineEmits(["update:loading"]);
const props = defineProps({
    loading: { required: true },
});

const isLoading = computed({
    get() {
        return props.loading;
    },
    set(val) {
        emit("update:loading", val);
    }
});

const currRtu = computed(() => monitoringRtuStore.rtu);

const isNumberEmpty = numb => numb === null || numb === undefined;
const tableData = computed(() => {
    const costMonthly = monitoringRtuStore.plnCostMonthly;
    if(!costMonthly)
        return [];
    return costMonthly.map((item, index) => {
        return {
            no: index + 1,
            periode: item.year && item.month ? Number(`${ item.year }${ item.month }`) : (item.year || 0),
            periodeText: `${ item?.year } ${ item?.month_name }`,
            kwh: item.kwh_usage,
            kwhText: isNumberEmpty(item?.kwh_usage) ? "-" : toNumberText(item.kwh_usage),
            lwbp: item.lwbp,
            lwbpText: isNumberEmpty(item?.lwbp) ? "-" : toNumberText(item.lwbp),
            wbp: item.wbp,
            wbpText: isNumberEmpty(item?.wbp) ? "-" : toNumberText(item.wbp),
            cost: item.kwh_cost,
            costText: isNumberEmpty(item?.kwh_cost) ? "-" : "Rp" + toNumberText(item.kwh_cost),
        };
    });
});
</script>
<template>
    <div class="card">
        <div class="card-header">
            <h5>Penggunaan KwH & Estimasi Biaya</h5>
            <span>
                Berikut data KwH penggunaan Listrik
                <span v-if="currRtu">di {{ currRtu?.lokasi }}</span>
                dengan perhitungan LWBP dan WBP
            </span>
        </div>
        <div v-if="isLoading" class="card-body">
            <div v-for="m in 4" :class="{ 'mb-2': m < 4 }" class="row">
                <div v-for="n in 4" class="col"><Skeleton /></div>
            </div>
        </div>
        <div v-else class="card-body pt-0">
            <p v-if="tableData.length < 1" class="text-center text-muted">Tidak ada data.</p>
            <DataTable v-else :value="tableData" showGridlines paginator :rows="10"
                responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" sortable />
                <Column field="periodeText" header="Periode" sortable sortField="periode" class="f-w-600" />
                <Column field="kwhText" header="Total Nilai KwH" sortable sortField="kwh" class="text-end" />
                <Column field="lwbpText" header="LWBP" sortable sortField="lwbp" class="text-end" />
                <Column field="wbpText" header="WBP" sortable sortField="wbp" class="text-end" />
                <Column field="costText" header="Total Tagihan" sortable sortField="cost" class="text-end f-w-600" />
            </DataTable>
        </div>
    </div>
</template>