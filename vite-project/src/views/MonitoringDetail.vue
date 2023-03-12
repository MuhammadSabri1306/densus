<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useMonitoringStore } from "@stores/monitoring";
import Skeleton from "primevue/skeleton";

import CardCurrKwh from "@components/CardCurrKwh.vue";
import CardTotalKwh from "@components/CardTotalKwh.vue";
import CardListrikCost from "@components/CardListrikCost.vue";
import CardSolarCost from "@components/CardSolarCost.vue";
import ChartKwhDaily from "@components/ChartKwhDaily.vue";
import CardKwhUsageToday from "@components/CardKwhUsageToday.vue";
import CardTotalEnergyCost from "@components/CardTotalEnergyCost.vue";
import CardMonitoringAlert from "@components/CardMonitoringAlert.vue";
import ChartSavingEnergyResult from "@components/ChartSavingEnergyResult.vue";
import ChartEnergyCostEstimation from "@components/ChartEnergyCostEstimation.vue";
import DatatableDashboard1 from "@components/DatatableDashboard1.vue";
import DatatableDashboard2 from "@components/DatatableDashboard2.vue";

const route = useRoute();
const rtuCode = computed(() => route.params.rtuCode);

const monitoringStore = useMonitoringStore();
const currRtu = ref({});
const isCurrInfoLoaded = ref(false);

monitoringStore.getRtuDetail(rtuCode.value)
    .then(data => {
        currRtu.value = {
            name: data.NAMA_RTU,
            location: data.LOKASI,
            datelName: data.DATEL,
            witelName: data.WITEL,
            divreName: data.DIVRE
        };
        isCurrInfoLoaded.value = true;
    });
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="colsm-12">
                        <h3 v-if="isCurrInfoLoaded">
                            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">{{ currRtu.location }}</span>
                        </h3>
                        <Skeleton v-else height="1.5rem" width="12rem" class="mb-2" />
                        <div v-if="isCurrInfoLoaded" class="d-inline-flex align-items-center ms-4">
                            <span>{{ currRtu.divreName }}</span>
                            <VueFeather type="chevrons-right" size="1.2rem" class="mx-2" />
                            <span>{{ currRtu.witelName }}</span>
                        </div>
                        <Skeleton v-else height="1rem" width="20rem" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-md-8">
                    <div v-if="isCurrInfoLoaded">
                        <Suspense>
                            <CardMonitoringAlert :rtuCode="rtuCode" :rtuLocation="currRtu.location" />
                            <template #fallback>
                                <Skeleton width="100%" height="20rem" class="mb-4" />
                            </template>
                        </Suspense>
                    </div>
                    <Suspense>
                        <ChartKwhDaily :rtuCode="rtuCode" />
                        <template #fallback>
                            <div class="card income-card">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <h5>KwH Usage Chart</h5>
                                    </div>
                                    <div class="card-body">
                                        <Skeleton width="100%" height="380px" class="mb-4" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
                <div class="col-md-4">
                    <Suspense>
                        <CardCurrKwh :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </Suspense>
                    <Suspense>
                        <CardTotalKwh :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </Suspense>
                    <Suspense>
                        <CardListrikCost :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </Suspense>
                    <Suspense>
                        <CardSolarCost :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </Suspense>
                    <div class="row">
                        <div class="col-md">
                            <Suspense>
                                <CardKwhUsageToday :rtuCode="rtuCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" class="mb-4" />
                                </template>
                            </Suspense>
                        </div>
                        <div class="col-md">
                            <Suspense>
                                <CardTotalEnergyCost :rtuCode="rtuCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" class="mb-4" />
                                </template>
                            </Suspense>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <Suspense>
                        <ChartSavingEnergyResult :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="30rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-md-6">
                    <Suspense>
                        <ChartEnergyCostEstimation :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="30rem" class="mb-4" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-12">
                    <Suspense>
                        <DatatableDashboard1 :rtuCode="rtuCode" />
                        <template #fallback>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
                <div class="col-12">
                    <Suspense>
                        <DatatableDashboard2 :rtuCode="rtuCode" />
                        <template #fallback>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                        <div class="col"><Skeleton /></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
            </div>
        </div>
    </div>
</template>