<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useMonitoringStore } from "@/stores/monitoring";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";

import CardPueRtuInfo from "@/components/CardPueRtuInfo.vue";
import CardPueCurrent from "@/components/CardPueCurrent.vue";
import CardPueMax from "@/components/CardPueMax.vue";
import ChartPueValue from "@/components/ChartPueValue.vue";
import CardContentPueAverage from "@/components/CardContentPueAverage.vue";
import CardContentPuePerformance from "@/components/CardContentPuePerformance.vue";
import DatatablePueValue from "@/components/DatatablePueValue.vue";

const route = useRoute();
const rtuCode = computed(() => route.params.rtuCode);

const pueData = ref({});

const currPue = computed(() => pueData.value.currValue || {});
const maxPue = computed(() => pueData.value.maxValue || {});
const valueOnYear = computed(() => pueData.value.valueOnYear?.data || []);
const yearOfValue = computed(() => pueData.value.valueOnYear?.year || null);
const averages = computed(() => pueData.value.averages || {});
const performances = computed(() => pueData.value.performances || {});

const isLoading = ref(true);
const monitoringStore = useMonitoringStore();

monitoringStore.getPueDetail(rtuCode.value, data => {
    pueData.value = data;
    isLoading.value = false;
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
                    <CardPueRtuInfo />
                    <CardPueCurrent v-if="!isLoading" :value="currPue" />
                    <Skeleton v-else width="100%" height="5rem" class="mb-4" />
                    <CardPueMax v-if="!isLoading" :value="maxPue" />
                    <Skeleton v-else width="100%" height="5rem" class="mb-4" />
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 v-if="yearOfValue">Nilai PUE tahun {{ yearOfValue }}</h5>
                            <h5 v-else>Nilai PUE tahun ini</h5>
                        </div>
                        <div class="card-body">
                            <ChartPueValue v-if="!isLoading" :pueValues="valueOnYear" />
                            <Skeleton v-else width="100%" height="380px" class="mb-4" />
                        </div>
                    </div>
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
                        <div class="card-header pb-0">
                            <h5 v-if="yearOfValue">Tabel nilai PUE tahun {{ yearOfValue }}</h5>
                            <h5 v-else>Tabel nilai PUE tahun ini</h5>
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