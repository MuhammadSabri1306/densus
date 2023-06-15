<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePueStore } from "@stores/pue";
import { usePueV2Store } from "@stores/pue-v2";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

import CardPueRtuInfo from "@components/CardPueRtuInfo.vue";
import CardPueCurrent from "@components/CardPueCurrent.vue";
import CardPueMax from "@components/CardPueMax.vue";
import ChartPueValue from "@components/ChartPueValue.vue";
import CardContentPueAverage from "@components/CardContentPueAverage.vue";
import CardContentPuePerformance from "@components/CardContentPuePerformance.vue";
import DatatablePueValue from "@components/DatatablePueValue.vue";

const route = useRoute();
const rtuCode = computed(() => route.params.rtuCode);

const pueStore = usePueStore();
const pue2Store = usePueV2Store();

const valueOnYear = computed(() => pueStore.pue ? pueStore.pue.valueOnYear : []);

const getYear = computed(() => {
    const val = pueStore.pue;
    if(!val) return null;
    
    const timestamp = val.valueOnYear?.timestamp;
    if(!timestamp) return null;
    
    const date = new Date(timestamp);
    return date ? date.getFullYear() : null;
});

const isLoading = ref(true);
setTimeout(() => pueStore.fetchOnRtu(rtuCode.value, false, () => isLoading.value = false), 1000);

pue2Store.setCurrentZone({ rtu: rtuCode.value });

const showDialogExport = ref(false);
const exportOptParams = computed(() => {
    const rtu = route.params.rtuCode;
    return "rtu=" + rtu;
});
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="feather" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">PUE Online</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE', 'PUE Online', 'RTU']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <CardPueRtuInfo />
                    <CardPueCurrent />
                    <CardPueMax title="Nilai PUE tertinggi tahun ini" />
                </div>
                <div class="col-md-8">
                    <ChartPueValue />
                </div>
                <div class="col-md-6">
                    <CardContentPueAverage />
                </div>
                <div class="col-md-6">
                    <CardContentPuePerformance />
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0 d-flex align-items-center">
                            <h5 v-if="getYear">Tabel nilai PUE tahun {{ getYear }}</h5>
                            <h5 v-else>Tabel nilai PUE tahun ini</h5>
                            <button type="button" @click="showDialogExport = true" class="btn-icon ms-auto px-3">
                                <VueFeather type="download" size="1em" />
                                <span class="ms-2">Export</span>
                            </button>
                        </div>
                        <div v-if="!isLoading" class="card-body">
                            <DatatablePueValue :pueValues="valueOnYear" />
                        </div>
                        <div v-else class="card-body">
                            <div v-for="i in 4" class="row mb-3">
                                <div class="col"><Skeleton /></div>
                                <div class="col"><Skeleton /></div>
                                <div class="col"><Skeleton /></div>
                                <div class="col"><Skeleton /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/pue/rtu" title="Export Data PUE Online"
            useYear requireYear initCurrDate :optionalParams="exportOptParams" @close="showDialogExport = false" />
    </div>
</template>
<style scoped>

:deep(th):first-child {
    width: 1%;
}

</style>