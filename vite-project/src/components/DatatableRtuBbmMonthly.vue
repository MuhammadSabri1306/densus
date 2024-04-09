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

const descrText = computed(() => {
    const rtu = monitoringRtuStore.rtu;
    const bbmPrice = monitoringRtuStore.bbmPrice;
    const costMonthly = monitoringRtuStore.bbmCostMonthly;

    let yearsText = null;
    if(costMonthly && costMonthly.length > 0) {
        const yearsRange = costMonthly
            .reduce((years, item) => {
                if(item?.year && years.indexOf(item.year) < 0)
                    years.push(item.year);
                return years;
            }, [])
            .sort((x, y) => x - y);
        yearsText = `${ yearsRange[0] }`;
        if(yearsRange.length > 1)
            yearsText += ` - ${ yearsRange[yearsRange.length - 1] }`;
    }

    let text = "Berikut historis penggunaan genset dan estimasi bahan bakarnya";
    if(rtu?.lokasi)
        text += ` di ${ rtu.lokasi }`;
    if(yearsText)
        text += ` pada ${ yearsText }`;
    if(bbmPrice)
        text += ` dengan harga Solar Industri beserta pajak per liternya adalah Rp ${ toIdrCurrency(bbmPrice) }`;
    return `${ text }.`;
});

const isNumberEmpty = numb => numb === null || numb === undefined;
const tableData = computed(() => {
    const costMonthly = monitoringRtuStore.bbmCostMonthly;
    if(!costMonthly)
        return [];
    return costMonthly.map((item, index) => {
        return {
            no: index + 1,
            periode: item.year && item.month ? Number(`${ item.year }${ item.month }`) : (item.year || 0),
            periodeText: `${ item?.year } ${ item?.month_name }`,
            gensetCount: item.deg_on_count,
            gensetCountText: isNumberEmpty(item?.deg_on_count) ? "-" : toNumberText(item.deg_on_count),
            durationHours: item.duration_ms,
            durationHoursText: isNumberEmpty(item?.duration_ms) ? "-" : toNumberText(item.duration_ms / 1000 / 3600),
            durationMinutes: item.duration_ms,
            durationMinutesText: isNumberEmpty(item?.duration_ms) ? "-" : toNumberText(item.duration_ms / 1000 / 60),
            bbmUsage: item.bbm_usage,
            bbmUsageText: isNumberEmpty(item?.bbm_usage) ? "-" : toNumberText(item.bbm_usage),
            bbmCost: item.bbm_cost,
            bbmCostText: isNumberEmpty(item?.bbm_cost) ? "-" : "Rp" + toNumberText(item.bbm_cost),
        };
    });
});
</script>
<template>
    <div class="card">
        <div class="card-header">
            <h5>Konsumsi Bahan Bakar Genset</h5>
            <span>{{ descrText }}</span>
        </div>
        <div v-if="isLoading" class="card-body">
            <div v-for="m in 4" :class="{ 'mb-2': m < 4 }" class="row">
                <div v-for="n in 4" class="col"><Skeleton /></div>
            </div>
        </div>
        <div v-else class="card-body pt-0">
            <p v-if="tableData.length < 1" class="text-center text-muted">Tidak ada data.</p>
            <DataTable v-else :value="tableData" showGridlines :paginator="true" :rows="10"
                responsiveLayout="scroll" class="table-sm">
                <Column field="no" header="No" sortable />
                <Column field="periodeText" header="Periode" sortable sortField="periode" class="f-w-600" />
                <Column field="gensetCountText" header="Genset ON Count" sortable sortField="gensetCount" class="text-end" />
                <Column field="durationHoursText" header="Jam Jalan" sortable sortField="durationHours" class="text-end" />
                <Column field="durationMinutesText" header="Menit Jalan" sortable sortField="durationMinutes" class="text-end" />
                <Column field="bbmUsageText" header="Liter Dikonsumsi" sortable sortField="bbmUsage" class="text-end" />
                <Column field="bbmCostText" header="Liter Dikonsumsi" sortable sortField="bbmCost" class="text-end" />
            </DataTable>
        </div>
    </div>
</template>