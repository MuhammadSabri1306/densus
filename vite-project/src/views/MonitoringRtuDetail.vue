<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import Skeleton from "primevue/skeleton";
import CardRtuKwhDescr from "@components/CardRtuKwhDescr.vue";
import ChartRtuKwhDaily from "@components/ChartRtuKwhDaily.vue";
import CardRtuCurrKwh from "@components/CardRtuCurrKwh.vue";
import CardRtuKwhCurrMonth from "@components/CardRtuKwhCurrMonth.vue";
import CardRtuKwhCostCurrYear from "@components/CardRtuKwhCostCurrYear.vue";
import CardRtuBbmCostCurrYear from "@components/CardRtuBbmCostCurrYear.vue";
import CardRtuKwhUsageToday from "@components/CardRtuKwhUsageToday.vue";
import CardRtuTotalEnergyCost from "@components/CardRtuTotalEnergyCost.vue";
import ChartRtuKwhSaving from "@components/ChartRtuKwhSaving.vue";
import ChartRtuEnergyCostEstimation from "@components/ChartRtuEnergyCostEstimation.vue";
import DatatableRtuPlnMonthly from "@components/DatatableRtuPlnMonthly.vue";
import DatatableRtuBbmMonthly from "@components/DatatableRtuBbmMonthly.vue";

const route = useRoute();
const rtuCode = computed(() => route.params.rtuCode);

const monitoringRtuStore = useMonitoringRtuStore();
const rtu = computed(() => monitoringRtuStore.rtu);

const isRtuLoading = ref(false);
const isCurrKwhLoading = ref(false);
const isEnergyCostLoading = ref(false);

let currFullPath = route.fullPath;
const setupDelayableFetch = (timeMs, callFetcher) => {
    setTimeout(() => {
        if(currFullPath == route.fullPath)
            callFetcher();
    }, timeMs);
};

if(rtuCode.value != monitoringRtuStore.rtuKey) {

    monitoringRtuStore.setRtuKey(rtuCode.value);

    isRtuLoading.value = true;
    isCurrKwhLoading.value = true;
    monitoringRtuStore.fetchRtu(() => {
        isRtuLoading.value = false;
        if(rtu.value?.port_kwh)
            monitoringRtuStore.fetchRealtimeKwh(rtu.value.port_kwh, () => isCurrKwhLoading.value = false);
    });
    // setupDelayableFetch(3000, () => {
    //     monitoringRtuStore.fetchRtu(() => {
    //         isRtuLoading.value = false;
    //         if(rtu.value?.port_kwh)
    //             monitoringRtuStore.fetchRealtimeKwh(rtu.value.port_kwh, () => isCurrKwhLoading.value = false);
    //     });
    // });
    
    isEnergyCostLoading.value = true;
    setupDelayableFetch(1000, () => monitoringRtuStore.fetchEnergyCost(() => isEnergyCostLoading.value = false));

}
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-12">
                        <h3>
                            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Monitoring RTU</span>
                        </h3>
                        <Skeleton v-if="isRtuLoading" height="1rem" width="20rem" class="mt-1 ms-4" />
                        <ol v-else-if="rtu" class="breadcrumb ms-4">
                            <li class="breadcrumb-item"><span>{{ rtu.divre_name }}</span></li>
                            <li class="breadcrumb-item"><span>{{ rtu.witel_name }}</span></li>
                            <li class="active breadcrumb-item"><span>{{ rtu.rtu_kode }}</span></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">

                <div class="col-md-8">
                    <CardRtuKwhDescr v-model:loading="isEnergyCostLoading" />
                    <ChartRtuKwhDaily v-model:loading="isEnergyCostLoading" />
                </div>

                <div class="col-md-4">

                    <CardRtuCurrKwh v-model:loading="isCurrKwhLoading" />

                    <CardRtuKwhCurrMonth v-model:loading="isEnergyCostLoading" />

                    <CardRtuKwhCostCurrYear v-model:loading="isEnergyCostLoading" />

                    <CardRtuBbmCostCurrYear v-model:loading="isEnergyCostLoading" />

                    <div class="row">

                        <div class="col-12 col-xl">
                            <CardRtuKwhUsageToday v-model:loading="isEnergyCostLoading" />
                        </div>

                        <div class="col-12 col-xl">
                            <CardRtuTotalEnergyCost v-model:loading="isEnergyCostLoading" />
                        </div>

                    </div>

                </div>

                <div class="col-md-6">
                    <ChartRtuKwhSaving v-model:loading="isEnergyCostLoading" />
                </div>

                <div class="col-md-6">
                    <ChartRtuEnergyCostEstimation v-model:loading="isEnergyCostLoading" />
                </div>

                <div class="col-12">
                    <DatatableRtuPlnMonthly v-model:loading="isEnergyCostLoading" />
                </div>

                <div class="col-12">
                    <DatatableRtuBbmMonthly v-model:loading="isEnergyCostLoading" />
                </div>

            </div>
        </div>
    </div>
</template>