<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePueStore } from "@stores/pue";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";

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
const requestLevel = computed(() => pueStore.pue ? pueStore.pue.req_level : null);

const latestValue = computed(() => pueStore.pue ? pueStore.pue.latestValue : {});
const maxPue = computed(() => pueStore.pue ? pueStore.pue.maxValue : {});
const valueOnYear = computed(() => pueStore.pue ? pueStore.pue.valueOnYear : []);
const averages = computed(() => pueStore.pue ? pueStore.pue.averages : {});
const performances = computed(() => pueStore.pue ? pueStore.pue.performances : {});

const getYear = computed(() => {
    const val = pueStore.pue;
    if(!val) return null;
    
    const timestamp = val.valueOnYear?.timestamp;
    if(!timestamp) return null;
    
    const date = new Date(timestamp);
    return date ? date.getFullYear() : null;
});

const isLoading = ref(true);
pueStore.fetchOnRtu(rtuCode.value, false, () => isLoading.value = false);

pueStore.setCurrentZone({ rtu: rtuCode.value });
const excelExportUrl = computed(() => pueStore.excelExportUrl);
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
                    <CardPueRtuInfo :data="requestLevel" />
                    <CardPueCurrent />
                    <CardPueMax v-if="!isLoading" :value="maxPue" title="Nilai PUE tertinggi tahun ini" />
                    <Skeleton v-else width="100%" height="5rem" class="mb-4" />
                </div>
                <div class="col-md-8">
                    <ChartPueValue />
                </div>
                <div class="col-md-6">
                    <CardContentPueAverage v-if="!isLoading" :value="averages" />
                    <Skeleton v-else width="100%" height="190px" class="mb-4" />
                </div>
                <div class="col-md-6">
                    <CardContentPuePerformance v-if="!isLoading" :value="performances" />
                    <Skeleton v-else width="100%" height="190px" class="mb-4" />
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0 d-flex align-items-center">
                            <h5 v-if="getYear">Tabel nilai PUE tahun {{ getYear }}</h5>
                            <h5 v-else>Tabel nilai PUE tahun ini</h5>
                            <a :href="excelExportUrl" target="_blank" class="btn-icon ms-auto">
                                <VueFeather type="download" size="1.2em" />
                                <span class="ms-1">Download</span>
                            </a>
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
    </div>
</template>
<style scoped>

:deep(th):first-child {
    width: 1%;
}

</style>