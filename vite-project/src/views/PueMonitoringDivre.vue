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
import DatatablePueOnSto from "@components/DatatablePueOnSto.vue";

const route = useRoute();
const divreCode = computed(() => route.params.divreCode);

const pueStore = usePueStore();
const requestLevel = computed(() => pueStore.pue ? pueStore.pue.req_level : null);

const latestAvgValueOnZone = computed(() => pueStore.pue ? pueStore.pue.latestAvgValueOnZone : {});
const maxPue = computed(() => pueStore.pue ? pueStore.pue.maxValue : {});
const valueOnYear = computed(() => pueStore.pue ? pueStore.pue.avgValueOnYear?.data : []);
const averages = computed(() => pueStore.pue ? pueStore.pue.averages : {});
const performances = computed(() => pueStore.pue ? pueStore.pue.performances : {});
const stoValueOnYear = computed(() => pueStore.pue ? pueStore.pue.stoValueOnYear?.data : []);

const getYear = computed(() => {
    const val = pueStore.pue;
    if(!val) return null;
    
    const timestamp = val.avgValueOnYear?.timestamp;
    if(!timestamp) return null;
    
    const date = new Date(timestamp);
    return date ? date.getFullYear() : null;
});

const isLoading = ref(true);
pueStore.fetchOnDivre(divreCode.value, false, () => isLoading.value = false);

pueStore.setCurrentZone({ divre: divreCode.value });
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
                            <span class="middle ms-3">Monitoring PUE</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE', 'RTU']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <CardPueRtuInfo :data="requestLevel" />
                    <CardPueCurrent />
                    <CardPueMax v-if="!isLoading" :value="maxPue" title="Nilai Rata-Rata PUE tertinggi tahun ini" />
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
                    <div class="card border-0">
                        <div class="card-header border-top border-start border-end d-flex align-items-center">
                            <h5 v-if="getYear">Tabel nilai PUE tahun {{ getYear }}</h5>
                            <h5 v-else>Tabel nilai PUE tahun ini</h5>
                            <a :href="excelExportUrl" target="_blank" class="btn-icon ms-auto">
                                <VueFeather type="download" size="1.2em" />
                                <span class="ms-1">Download</span>
                            </a>
                        </div>
                        <div v-if="!isLoading" class="card-body p-0">
                            <DatatablePueOnSto :list="stoValueOnYear" level="divre" />
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