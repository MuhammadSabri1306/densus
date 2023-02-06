<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Skeleton from "primevue/skeleton";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

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

const stoCode = "BAL";
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="">
                <div class="row">
                    <div class="col-sm-6">
                        <h3> STO BALAI KOTA</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Divisi Telkom Regional VII</a>
                            </li>
                            <li class="breadcrumb-item">Witel Makassar</li>
                            <li class="breadcrumb-item active"> STO BALAI KOTA</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardCurrKwh :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardTotalKwh :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardListrikCost :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <Suspense>
                        <CardSolarCost :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="5rem" />
                        </template>
                    </Suspense>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-8 box-col-8 des-xl-100 dashboard-sec">
                    <Suspense>
                        <ChartKwhDaily :stoCode="stoCode" />
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
                                <CardKwhUsageToday :stoCode="stoCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" />
                                </template>
                            </Suspense>
                        </div>
                        <div class="col-xl-6 col-md-3 col-sm-2 box-col-3 des-xl-25 rate-sec">
                            <Suspense>
                                <CardTotalEnergyCost :stoCode="stoCode" />
                                <template #fallback>
                                    <Skeleton width="100%" height="10rem" />
                                </template>
                            </Suspense>
                        </div>
                        <div class="col-xl-12 col-md box-col-12 des-xl-50">
                            <CardMonitoringAlert :stoCode="stoCode" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 box-col-4 des-xl-100 dashboard-sec">
                    <Suspense>
                        <ChartSavingEnergyResult :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="10rem" />
                        </template>
                    </Suspense>
                </div>
                <div class="col-xl-8 box-col-8 des-xl-100">
                    <Suspense>
                        <ChartEnergyCostEstimation :stoCode="stoCode" />
                        <template #fallback>
                            <Skeleton width="100%" height="10rem" />
                        </template>
                    </Suspense>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <Suspense>
                        <DatatableDashboard1 :stoCode="stoCode" />
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
                        <DatatableDashboard2 :stoCode="stoCode" />
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