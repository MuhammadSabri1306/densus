<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useMonitoringStore } from "@/stores/monitoring";
import Skeleton from "primevue/skeleton";

import HeadingMonitoringDetail from "@/components/HeadingMonitoringDetail.vue";
import CardCurrKwh from "@/components/CardCurrKwh.vue";
import CardTotalKwh from "@/components/CardTotalKwh.vue";
import CardListrikCost from "@/components/CardListrikCost.vue";
import CardSolarCost from "@/components/CardSolarCost.vue";
import ChartKwhDaily from "@/components/ChartKwhDaily.vue";
import CardKwhUsageToday from "@/components/CardKwhUsageToday.vue";
import CardTotalEnergyCost from "@/components/CardTotalEnergyCost.vue";
import CardMonitoringAlert from "@/components/CardMonitoringAlert.vue";
import ChartSavingEnergyResult from "@/components/ChartSavingEnergyResult.vue";
import ChartEnergyCostEstimation from "@/components/ChartEnergyCostEstimation.vue";
import DatatableDashboard1 from "@/components/DatatableDashboard1.vue";
import DatatableDashboard2 from "@/components/DatatableDashboard2.vue";

const route = useRoute();
const rtuCode = computed(() => route.params.rtuCode);

const monitoringStore = useMonitoringStore();

const currRtu = ref(null);
const kwhReal = ref(null);
const kwhTotal = ref(null);
const kwhToday = ref(null);
const bbmCost = ref(null);
const dailyChart = ref(null);
const saving = ref(null);
const plnData = ref(null);
const degData = ref(null);

const loadingState = reactive({
    currRtu: false,
    kwhReal: false,
    kwhTotal: false,
    kwhToday: false,
    bbmCost: false,
    dailyChart: false,
    saving: false,
    plnData: false,
    degData: false,
});

const dataResolver = {
    currRtu: async () => {
        currRtu.value = await monitoringStore.getRtuDetail(rtuCode.value);
        loadingState.currRtu = true;
    },
    kwhReal: async () => {
        kwhReal.value = await monitoringStore.getKwhReal(rtuCode.value);
        loadingState.kwhReal = true;
    },
    kwhTotal: async () => {
        kwhTotal.value = await monitoringStore.getKwhTotal(rtuCode.value);
        loadingState.kwhTotal = true;
    },
    kwhToday: async () => {
        kwhToday.value = await monitoringStore.getKwhToday(rtuCode.value);
        loadingState.kwhToday = true;
    },
    bbmCost: async () => {
        bbmCost.value = await monitoringStore.getBbmCost(rtuCode.value);
        loadingState.bbmCost = true;
    },
    dailyChart: async () => {
        dailyChart.value = await monitoringStore.getChartDataDaily(rtuCode.value);
        loadingState.dailyChart = true;
    },
    saving: async () => {
        saving.value = await monitoringStore.getSavingPercent(rtuCode.value);
        loadingState.saving = true;
    },
    plnData: async () => {
        plnData.value = await monitoringStore.getTableData(rtuCode.value);
        loadingState.plnData = true;
    },
    degData: async () => {
        degData.value = await monitoringStore.getDegTableData(rtuCode.value);
        loadingState.degData = true;
    },
};

/* HeadingMonitoringDetail */
const headingElm = ref(null);
const headingDataLoader = () => {
    if(loadingState.currRtu)
        headingElm.value.resolve(currRtu.value);
};

/* CardCurrKwh */
const cardAElm = ref(null);
const cardADataLoader = () => {
    if(loadingState.kwhReal)
        cardAElm.value.resolve(kwhReal.value);
};

/* CardTotalKwh */
const cardBElm = ref(null);
const cardBDataLoader = () => {
    if(loadingState.kwhTotal)
        cardBElm.value.resolve(kwhTotal.value);
};

/* CardListrikCost */
const cardCElm = ref(null);
const cardCDataLoader = () => {
    if(loadingState.plnData)
        cardCElm.value.resolve(plnData.value);

};

/* CardSolarCost */
const cardDElm = ref(null);
const cardDDataLoader = () => {
    if(loadingState.bbmCost)
        cardDElm.value.resolve(bbmCost.value);
};

/* ChartKwhDaily */
const chartAElm = ref(null);
const chartADataLoader = () => {
    if(loadingState.dailyChart)
        chartAElm.value.resolve(dailyChart.value);
};

/* CardKwhUsageToday */
const cardEElm = ref(null);
const cardEDataLoader = () => {
    if(loadingState.kwhToday)
        cardEElm.value.resolve(kwhToday.value);
};

/* CardTotalEnergyCost */
const cardFElm = ref(null);
const cardFDataLoader = () => {
    if(loadingState.bbmCost && loadingState.plnData)
        cardFElm.value.resolve(bbmCost.value, plnData.value);
};

/* ChartSavingEnergyResult */
const chartBElm = ref(null);
const chartBDataLoader = () => {
    if(loadingState.saving)
        chartBElm.value.resolve(saving.value);
};

/* ChartEnergyCostEstimation */
const chartCElm = ref(null);
const chartCDataLoader = () => {
    if(loadingState.plnData && loadingState.degData)
        chartCElm.value.resolve(plnData.value, degData.value);
};

/* CardMonitoringAlert */
const cardGElm = ref(null);
const cardGDataLoader = () => {
    if(loadingState.saving && loadingState.currRtu)
        cardGElm.value.resolve(saving.value, currRtu.value.location);
};

/* DatatableDashboard1 */
const tableAElm = ref(null);
const tableADataLoader = () => {
    if(loadingState.plnData)
        tableAElm.value.resolve(plnData.value);
};

/* DatatableDashboard2 */
const tableBElm = ref(null);
const tableBDataLoader = () => {
    if(loadingState.degData)
        tableBElm.value.resolve(degData.value);
};

const watcherSrc = () => {
    return {
        currRtu: loadingState.currRtu,
        kwhReal: loadingState.kwhReal,
        kwhTotal: loadingState.kwhTotal,
        kwhToday: loadingState.kwhToday,
        bbmCost: loadingState.bbmCost,
        dailyChart: loadingState.dailyChart,
        plnData: loadingState.plnData,
        degData: loadingState.degData,
    };
};

watch(watcherSrc, () => {
    headingDataLoader();
    cardADataLoader();
    cardBDataLoader();
    cardCDataLoader();
    cardDDataLoader();
    chartADataLoader();
    cardEDataLoader();
    cardFDataLoader();
    chartBDataLoader();
    chartCDataLoader();
    cardGDataLoader();
    tableADataLoader();
    tableBDataLoader();
});

const executeLoaderBatch = (...loaderList) => {
    const timeMultiplier = 1000;
    return new Promise((resolve) => {

        const listStatus = loaderList.map(() => false);
        const statusChecker = () => {
            for(let i=0; i< listStatus.length; i++) {
                if(!listStatus[i])
                    return;
            }
            resolve();
        };

        loaderList.forEach((loader, index) => {

            if(index === 0) {
                loader.then(statusChecker);
            } else {
                setTimeout(() => loader.then(statusChecker), (index * timeMultiplier));
            }

        });
    });
};

const isLoadingInitialized = ref(false);
onMounted(async () => {

    if(isLoadingInitialized.value)
        return;
    isLoadingInitialized.value = true;
    await dataResolver.currRtu();
    await dataResolver.kwhToday();
    await dataResolver.kwhTotal();
    await dataResolver.plnData();
    await dataResolver.kwhReal();
    await dataResolver.saving();
    await dataResolver.dailyChart();
    await dataResolver.bbmCost();
    await dataResolver.degData();
    // await executeLoaderBatch(dataResolver.currRtu, dataResolver.kwhToday, dataResolver.kwhTotal);
    // await executeLoaderBatch(dataResolver.plnData, dataResolver.kwhReal);
    // await executeLoaderBatch(dataResolver.dailyChart, dataResolver.bbmCost);
    // await executeLoaderBatch(dataResolver.saving, dataResolver.degData);
    
});
// dataResolver
// currRtu v
// kwhReal v
// kwhTotal v
// kwhToday v
// bbmCost v
// dailyChart v
// saving v
// plnData v
// degData v
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <HeadingMonitoringDetail ref="headingElm" class="colsm-12" />
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-md-8">

                    <CardMonitoringAlert ref="cardGElm">
                        <template #loading>
                            <Skeleton width="100%" height="20rem" class="mb-4" />
                        </template>
                    </CardMonitoringAlert>

                    <ChartKwhDaily ref="chartAElm">
                        <template #loading>
                            <Skeleton width="100%" height="380px" class="mb-4" />
                        </template>
                    </ChartKwhDaily>

                </div>
                <div class="col-md-4">

                    <CardCurrKwh ref="cardAElm">
                        <template #loading>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </CardCurrKwh>

                    <CardTotalKwh ref="cardBElm">
                        <template #loading>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </CardTotalKwh>

                    <CardListrikCost ref="cardCElm">
                        <template #loading>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </CardListrikCost>

                    <CardSolarCost ref="cardDElm">
                        <template #loading>
                            <Skeleton width="100%" height="5rem" class="mb-4" />
                        </template>
                    </CardSolarCost>

                    <div class="row">

                        <div class="col-md">
                            <CardKwhUsageToday ref="cardEElm">
                                <template #loading>
                                    <Skeleton width="100%" height="10rem" class="mb-4" />
                                </template>
                            </CardKwhUsageToday>
                        </div>

                        <div class="col-md">
                            <CardTotalEnergyCost ref="cardFElm">
                                <template #loading>
                                    <Skeleton width="100%" height="10rem" class="mb-4" />
                                </template>
                            </CardTotalEnergyCost>
                        </div>

                    </div>
                </div>

                <div class="col-md-6">
                    <ChartSavingEnergyResult ref="chartBElm">
                        <template #loading>
                            <Skeleton width="100%" height="30rem" />
                        </template>
                    </ChartSavingEnergyResult>
                </div>

                <div class="col-md-6">
                    <ChartEnergyCostEstimation ref="chartCElm">
                        <template #loading>
                            <Skeleton width="100%" height="30rem" class="mb-4" />
                        </template>
                    </ChartEnergyCostEstimation>
                </div>

                <div class="col-12">
                    <DatatableDashboard1 ref="tableAElm">
                        <template #loading>
                            <div class="card">
                                <div class="card-body">
                                    <div v-for="m in 4" :class="{ 'mb-2': m < 4 }" class="row">
                                        <div v-for="n in 4" class="col"><Skeleton /></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </DatatableDashboard1>
                </div>

                <div class="col-12">
                    <DatatableDashboard2 ref="tableBElm">
                        <template #loading>
                            <div class="card">
                                <div class="card-body">
                                    <div v-for="m in 4" :class="{ 'mb-2': m < 4 }" class="row">
                                        <div v-for="n in 4" class="col"><Skeleton /></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </DatatableDashboard2>
                </div>

            </div>
        </div>
    </div>
</template>