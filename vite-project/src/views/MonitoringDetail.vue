<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";

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
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>STO BALAI KOTA</h3>
                        <DashboardBreadcrumb :items="['Divisi Telkom Regional VII', 'Witel Makassar', 'STO BALAI KOTA']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardCurrKwh :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardTotalKwh :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardListrikCost :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardSolarCost :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-8 box-col-8 des-xl-100 dashboard-sec">
                    <Suspense>
                        <ChartKwhDaily :rtuCode="rtuCode" />
                        <template #fallback>
                            <div class="card income-card">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <h5>KwH Usage Chart</h5>
                                    </div>
                                    <div class="card-body">
                                        <Skeleton width="100%" height="380px" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
                <div class="col-xl-4 box-col-4 des-xl-100">
                    <div class="row">
                        <div class="col-xl-6 col-md-3 col-sm-2 box-col-3 des-xl-25 rate-sec">
                            <Suspense>
                                <CardKwhUsageToday :rtuCode="rtuCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" />
                                </template>
                            </Suspense>
                        </div>
                        <div class="col-xl-6 col-md-3 col-sm-2 box-col-3 des-xl-25 rate-sec">
                            <Suspense>
                                <CardTotalEnergyCost :rtuCode="rtuCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" />
                                </template>
                            </Suspense>
                        </div>
                        <div class="col-xl-12 col-md box-col-12 des-xl-50">
                            <CardMonitoringAlert :rtuCode="rtuCode" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 box-col-4 des-xl-100 dashboard-sec">
                    <Suspense>
                        <ChartSavingEnergyResult :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="10rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-xl-8 box-col-8 des-xl-100">
                    <Suspense>
                        <ChartEnergyCostEstimation :rtuCode="rtuCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="10rem" />
                        </template>
                    </Suspense>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
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
            </div>
            <div class="row">
                <div class="col-sm-12">
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