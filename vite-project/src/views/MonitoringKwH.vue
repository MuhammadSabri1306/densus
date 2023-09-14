<script setup>
import { ref, computed, reactive } from "vue";
import { useRoute } from "vue-router";
import { useKwhStore } from "@/stores/kwh";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { toNumberText, toIdrCurrency } from "@helpers/number-format";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@components/FilterGepeeV2.vue";
import { BuildingOfficeIcon, CalendarIcon } from "@heroicons/vue/24/outline";
import CardModern from "@components/CardModern.vue";
import Dropdown from "primevue/dropdown";
import Calendar from "primevue/calendar";
import Skeleton from "primevue/skeleton";
import DatatableKwhDaily from "@/components/DatatableKwhDaily.vue";
import ChartMonitoringKwhDaily from "@/components/ChartMonitoringKwhDaily.vue";
import DatatableKwhWeekly from "@/components/DatatableKwhWeekly.vue";
import ChartMonitoringKwhWeekly from "@/components/ChartMonitoringKwhWeekly.vue";
import DatatableKwhMonthly from "@/components/DatatableKwhMonthly.vue";
import ChartMonitoringKwhMonthly from "@/components/ChartMonitoringKwhMonthly.vue";

const viewStore = useViewStore();
const userStore = useUserStore();
const kwhStore = useKwhStore();

const periodicModes = kwhStore.periodicModes;
const periodicMode = ref("daily");

const tempFilter = reactive({
    periodicMode: "daily",
    viewMode: "table",
    date: new Date()
});

if(viewStore.filters.year)
    tempFilter.date.setFullYear(viewStore.filters.year);
if(viewStore.filters.month)
    tempFilter.date.setMonth(viewStore.filters.month - 1);

// hapus nanti
// if(!viewStore.filters.divre)
//     viewStore.setFilter({ divre: "TLK-r7000000" });

const tableKwhDaily = ref(null);
const chartKwhDaily = ref(null);
const isKwhDailyLoading = ref(false);
const fetchKwhDaily = () => {
    isKwhDailyLoading.value = true;
    kwhStore.getDaily(({ data }) => {
        tableKwhDaily.value.setSrcData(data);
        chartKwhDaily.value.setSrcData(data);
        isKwhDailyLoading.value = false;
    });
};

const tableKwhWeekly = ref(null);
const chartKwhWeekly = ref(null);
const isKwhWeeklyLoading = ref(false);
const fetchKwhWeekly = () => {
    isKwhWeeklyLoading.value = true;
    kwhStore.getWeekly(({ data }) => {
        tableKwhWeekly.value.setSrcData(data);
        chartKwhWeekly.value.setSrcData(data);
        isKwhWeeklyLoading.value = false;
    });
};

const tableKwhMonthly = ref(null);
const chartKwhMonthly = ref(null);
const isKwhMonthlyLoading = ref(false);
const fetchKwhMonthly = () => {
    isKwhMonthlyLoading.value = true;
    kwhStore.getMonthly(({ data }) => {
        tableKwhMonthly.value.setSrcData(data);
        chartKwhMonthly.value.setSrcData(data);
        isKwhMonthlyLoading.value = false;
    });
};

const momTargetArea = ref(null);
const momEntries = reactive({
    performance: null,
    kwh: null,
    cost: null
});

const isKwhMomLoading = ref(false);
const fetchMoM = () => {
    isKwhMomLoading.value = true;
    kwhStore.getMoM(({ data }) => {
        isKwhMomLoading.value = false;

        momEntries.performance = data?.mom.performance;
        momEntries.kwh = data?.mom.kwh;
        momEntries.cost = data?.mom.cost;

        if(data.target_area)
            momTargetArea.value = data.target_area;
    });
};

const locationKey = ref(null);
const appliedLocationKey = computed(() => {
    const userLevel = userStore.level;
    const userLocationId = userStore.locationId;
    const filter = kwhStore.filters;

    if(filter.witel || filter.divre)
        return filter.witel || filter.divre;
    if(userLevel == "nasional")
        return userLevel;
    if(userLocationId)
        return userLocationId;
    return null;
});

const filterAutoApply = () => true;
const isLoading = computed(() => {
    if(isKwhDailyLoading.value)
        return true;
    if(isKwhWeeklyLoading.value)
        return true;
    if(isKwhMonthlyLoading.value)
        return true;
    if(isKwhMomLoading.value)
        return true;
    return false;
});

const onFilterApply = filterValue => {

    filterValue.year = tempFilter.date.getFullYear();

    if(periodicMode.value != "monthly")
        filterValue.month = tempFilter.date.getMonth() + 1;

    viewStore.setFilter(filterValue);
    if(locationKey.value != appliedLocationKey.value) {
        fetchMoM();
        locationKey.value = appliedLocationKey.value;
        // setTimeout(fetchMoM, 500);
    }

    periodicMode.value = tempFilter.periodicMode;
    if(periodicMode.value == "daily") {
        fetchKwhDaily();
    } else if(periodicMode.value == "weekly") {
        fetchKwhWeekly();
    } else if(periodicMode.value == "monthly") {
        fetchKwhMonthly();
    }
};

const monthLabel = computed(() => {
    const monthList = viewStore.monthList;
    const currMonthIndex = (new Date()).getMonth();
    return {
        previous: monthList[currMonthIndex - 1],
        current: monthList[currMonthIndex]
    };
});

const location = computed(() => {
    const userLevel = userStore.level;
    const userLocation = userStore.location;
    const targetArea = momTargetArea.value;
    
    if(targetArea)
        return targetArea.witel_name || targetArea.divre_name;
    return userLevel == "nasional" ? "Nasional" : userLocation;
});

const currDay = computed(() => {
    return new Intl
        .DateTimeFormat("id", { dateStyle: "long" })
        .format(new Date());
});

const formatMomValue = val => {
    if(val === null)
        return "-";
    
    const isMinNumber = val < 0;
    if(isMinNumber)
        val = val * -1;
    return `${ (isMinNumber ? "-" : "+") } ${ toNumberText(val) }`;
};

const formatMomCost = val => {
    if(val === null)
        return "Rp -";
    
    const isMinNumber = val < 0;
    if(isMinNumber)
        val = val * -1;
    return `${ (isMinNumber ? "-" : "+") } Rp ${ toNumberText(val) }`;
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row g-5">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="battery-charging" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Monitoring KwH</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring KwH', location]" class="ms-4" />
                    </div>
                    <div class="col-auto ms-auto">
                        <div class="d-flex align-items-start">
                            <button class="btn btn-icon btn-warning text-dark me-3" type="button" :title="location">
                                <div class="badge badge-light text-dark">
                                    <span><BuildingOfficeIcon /></span>
                                </div>
                                &nbsp;{{ location }}
                            </button>
                            <button class="btn btn-icon btn-info" type="button" :title="currDay">
                                <div class="badge badge-light text-dark">
                                    <span><CalendarIcon /></span>
                                </div>
                                &nbsp;{{ currDay }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mb-4">
            <div v-show="isKwhMomLoading" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 md:tw-gap-8 xl:tw-gap-12">
                <Skeleton v-for="n in 3" height="10rem" class="mb-5" />
            </div>
            <div v-show="!isKwhMomLoading" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 md:tw-gap-8 xl:tw-gap-12 tw-items-start">
                <CardModern class="border-0" cardClass="bg-primary" contentClass="ps-0">
                    <template #content>
                        <p class="tw-mb-2">
                            <span class="!tw-font-semibold">Rata-Rata Performansi KwH</span><br>
                            <span>MoM {{ monthLabel.previous.name }} - {{ monthLabel.current.name }}</span>
                        </p>
                        <h6>{{ location }}</h6>
                        <p class="mb-0 text-end tw-font-bold tw-text-4xl">{{ formatMomValue(momEntries.performance) }}%</p>
                        <VueFeather type="trending-up" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern class="border-0" cardClass="bg-warning" contentClass="ps-0">
                    <template #content>
                        <p class="tw-mb-2 text-dark">
                            <span class="!tw-font-semibold">Rata-Rata Penghematan KwH</span><br>
                            <span>MoM {{ monthLabel.previous.name }} - {{ monthLabel.current.name }}</span>
                        </p>
                        <h6 class="text-dark">{{ location }}</h6>
                        <p class="mb-0 text-end tw-font-bold tw-text-4xl text-dark">{{ formatMomValue(momEntries.kwh) }} Kw</p>
                        <VueFeather type="filter" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
                <CardModern class="border-0" cardClass="bg-secondary" contentClass="ps-0">
                    <template #content>
                        <p class="tw-mb-2">
                            <span class="!tw-font-semibold">Rata-Rata Penghematan Tagihan Listrik</span><br>
                            <span>MoM {{ monthLabel.previous.name }} - {{ monthLabel.current.name }}</span>
                        </p>
                        <h6>{{ location }}</h6>
                        <p class="mb-0 text-end tw-font-bold tw-text-4xl">{{ formatMomCost(momEntries.cost) }}</p>
                        <VueFeather type="dollar-sign" stroke-width="1" class="icon-bg" />
                    </template>
                </CardModern>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 @apply="onFilterApply" :autoApply="filterAutoApply" divreColClass="col-md-4" witelColClass="col-md-4"
                rowClass="row justify-content-end align-items-end tw-gap-y-4">
                <template #filter3>
                    <div class="col-md-4 col-xl-2">
                        <div class="mb-2">
                            <label for="inputPeriode">Periode</label>
                            <!-- <ListboxFilter inputId="inputPeriode"  @change="onPeriodeChange" /> -->
                            <Dropdown v-model="tempFilter.periodicMode" :options="periodicModes" optionLabel="title" optionValue="value"
                                inputClass="form-control" class="d-flex" />
                        </div>
                    </div>
                </template>
                <template #filter4>
                    <div class="col-md-4 col-xl-2">
                        <div v-if="tempFilter.periodicMode != 'monthly'" class="mb-2">
                            <label for="inputMonth" class="required">Bulan</label>
                            <div class="d-grid">
                                <Calendar v-model="tempFilter.date" view="month" dateFormat="M yy" placeholder="Pilih Bulan"
                                    :class="{ 'p-invalid': !tempFilter.date }" inputId="inputMonth" inputClass="form-control" panelClass="filter-month" />
                            </div>
                        </div>
                        <div v-else class="mb-2">
                            <label for="inputYear" class="required">Tahun</label>
                            <div class="d-grid">
                                <Calendar v-model="tempFilter.date" view="year" dateFormat="yy" placeholder="Pilih Tahun"
                                    :class="{ 'p-invalid': !tempFilter.date }" inputId="inputYear" inputClass="form-control text-center" panelClass="tw-min-w-[14rem]" />
                            </div>
                        </div>
                    </div>
                </template>
                <template #action>
                    <div class="px-md-4">
                        <div class="d-flex">
                            <label id="labelViewOption" class="text-center mx-auto">View Option</label>
                        </div>
                        <div class="btn-group tw-grid tw-grid-cols-2" role="group" aria-labelledby="labelViewOption">
                            <button type="button" @click="tempFilter.viewMode = 'table'"
                                :class="(tempFilter.viewMode == 'table') ? 'btn-primary' : 'btn-light'"
                                class="btn">Table View</button>
                            <button type="button" @click="tempFilter.viewMode = 'chart'"
                                :class="(tempFilter.viewMode == 'chart') ? 'btn-primary' : 'btn-light'"
                                class="btn">Graphic View</button>
                        </div>
                    </div>
                </template>
            </FilterGepeeV2>
        </div>
        <div v-show="isLoading" class="container-fluid dashboard-default-sec pb-5">
            <div class="card card-body">
                <div class="row gy-4">
                    <div v-for="item in 24" class="col-3 col-lg-4">
                        <Skeleton />
                    </div>
                </div>
            </div>
        </div>
        <div v-show="!isLoading" class="container-fluid dashboard-default-sec pb-5">
            <!-- <DataTableActivityDashboardV2 ref="datatable" /> -->
            <DatatableKwhDaily ref="tableKwhDaily" v-show="periodicMode == 'daily' && tempFilter.viewMode == 'table'" />
            <ChartMonitoringKwhDaily ref="chartKwhDaily" v-show="periodicMode == 'daily' && tempFilter.viewMode == 'chart'" />
            <DatatableKwhWeekly ref="tableKwhWeekly" v-show="periodicMode == 'weekly' && tempFilter.viewMode == 'table'" />
            <ChartMonitoringKwhWeekly ref="chartKwhWeekly" v-show="periodicMode == 'weekly' && tempFilter.viewMode == 'chart'" />
            <DatatableKwhMonthly ref="tableKwhMonthly" v-show="periodicMode == 'monthly' && tempFilter.viewMode == 'table'" />
            <ChartMonitoringKwhMonthly ref="chartKwhMonthly" v-show="periodicMode == 'monthly' && tempFilter.viewMode == 'chart'" />
        </div>
    </div>
</template>
<style scoped>

.badge {
    @apply tw-h-6 tw-w-6 tw-rounded-full tw-flex tw-justify-center tw-items-center;
}

.badge :deep(svg) {
    @apply !tw-w-5 !tw-h-5;
}

.btn-icon {
    @apply tw-whitespace-nowrap tw-flex tw-justify-center tw-items-center tw-gap-2;
}

</style>