<script setup>
import { ref, computed } from "vue";
import { useMonitoringStore } from "@/stores/monitoring";
import { useViewStore } from "@/stores/view";
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import FilterRtuV2 from "@/components/FilterRtuV2.vue";

const monitoringStore = useMonitoringStore()
const viewStore = useViewStore();
const filterAutoApply = () => viewStore.filters.divre ? true : false;

const rtuShowedCount = ref(12);
const allRtuList = ref([]);
const rtuList = computed(() => {
    const list = allRtuList.value;
    const count = rtuShowedCount.value;
    return list.slice(0, count);
});

const isShowingAllRtu = computed(() => {
    const currCount = rtuShowedCount.value;
    const allRtus =  allRtuList.value;
    return currCount < allRtus.length ? false : true;
});

const isLoadingMore = ref(false);
const onShowMoreRtu = () => {
    isLoadingMore.value = true;
    const times = [300, 600, 900, 1200];
    const randomTime = times[Math.floor( (Math.random() * times.length) )];
    setTimeout(() => {
        const currCount = rtuShowedCount.value + 12;
        const allCount = allRtuList.value.length;
        rtuShowedCount.value = Math.min(currCount, allCount);
        isLoadingMore.value = false;
    }, randomTime);
};

const hasFetched = ref(false);
const isLoading = ref(false);
const onFilterApply = filterValue => {

    viewStore.setFilter(filterValue);
    if(!hasFetched.value)
        hasFetched.value = true;

    isLoading.value = true;
    rtuShowedCount.value = 12;
    monitoringStore.getRtuList(rtus => {
        allRtuList.value = rtus;
        isLoading.value = false;
    });

};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Daftar RTU</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring RTU', 'Daftar RTU']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterRtuV2 @apply="onFilterApply" :autoApply="filterAutoApply" divreColClass="col-12 col-md"
                witelColClass="col-12 col-md" />
        </div>
        <div v-if="hasFetched" class="container-fluid dashboard-default-sec">
            <div v-if="isLoading" class="row">
                <div v-for="n in 12" class="col-12 col-md-4 col-lg-3">
                    <div class="card">
                        <div class="card-body bg-white">
                            <div class="d-flex flex-column align-items-center">
                                <Skeleton shape="circle" size="40px" />
                                <Skeleton width="85%" height="1rem" class="mb-2 mt-3" borderRadius="16px" />
                                <Skeleton width="40%" height="1rem" class="mb-2" borderRadius="16px" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="rtuList.length < 1">
                <p class="text-center text-muted">Tidak ada data RTU.</p>
            </div>
            <div v-else class="tw-pb-[12rem]">
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-6">
                    <div v-for="item in rtuList" class="rtu-wrapper">
                        <RouterLink v-if="item.is_available" :to="'/monitoring-rtu/'+item.rtu_kode" class="card btn-outline-primary" :title="item.rtu_name">
                            <div class="card-body bg-success">
                                <div class="d-flex flex-column justify-content-center align-items-center">
                                    <VueFeather type="hard-drive" width="40px" height="40px" />
                                    <h6 class="text-center mb-0 mt-3">{{ item.rtu_kode }}</h6>
                                    <p class="text-center mb-0">{{ item.rtu_name }}</p>
                                </div>
                            </div>
                        </RouterLink>
                        <div v-else class="card">
                            <div class="card-body bg-info">
                                <div class="d-flex flex-column justify-content-center align-items-center">
                                    <VueFeather type="hard-drive" width="40px" height="40px" />
                                    <h6 class="text-center mb-0 mt-3">{{ item.rtu_kode }}</h6>
                                    <p class="text-center mb-0">{{ item.rtu_name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!isShowingAllRtu" class="d-flex justify-content-center mt-5">
                    <button type="button" @click="onShowMoreRtu" :class="{ 'btn-loading': isLoadingMore }" class="btn btn-light">Muat lebih banyak...</button>
                </div>
            </div>
        </div>
    </div>
</template>