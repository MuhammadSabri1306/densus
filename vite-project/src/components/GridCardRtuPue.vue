<script setup>
import { ref, computed } from "vue";
import { useMonitoringStore } from "@stores/monitoring";
import Skeleton from "primevue/skeleton";

const rtuList = ref([]);
const isLoading = ref(false);

const monitoringStore = useMonitoringStore();
const fetchRtu = (divreCode = null, witelCode = null) => {
    rtuList.value = [];
    isLoading.value = true;
    
    const filter = { divre: divreCode, witel: witelCode };
    monitoringStore.getRtuListPue(filter)
        .then(data => {
            rtuList.value = data;
            isLoading.value = false;
        });
};

const formatRtuList = computed(() => {
    return rtuList.value.map(item => {
        return {
            isAvailable: item.AVAILABLE,
            route: "/monitoring/" + item.RTU_ID,
            rtuName: item.NAMA_RTU,
            rtuId: item.RTU_ID
        };
    });
});

defineExpose({ fetchRtu });
</script>
<template>
    <div class="container-fluid dashboard-default-sec">
        <div v-if="isLoading" class="row">
            <div v-for="n in 12" class="col-6 col-md-4 col-lg-3">
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
        <div v-if="rtuList.length > 0 && !isLoading" class="row">
            <div v-for="item in formatRtuList" class="col-6 col-md-4 col-lg-3">
                <RouterLink v-if="item.isAvailable" :to="'/pue/'+item.rtuId" class="card btn-outline-primary" :title="item.rtuName">
                    <div class="card-body bg-success">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <VueFeather type="hard-drive" width="40px" height="40px" />
                            <h6 class="text-center mb-0 mt-3">{{ item.rtuId }}</h6>
                            <p class="text-center mb-0">{{ item.rtuName }}</p>
                        </div>
                    </div>
                </RouterLink>
                <div v-else class="card">
                    <div class="card-body bg-info">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <VueFeather type="hard-drive" width="40px" height="40px" />
                            <h6 class="text-center mb-0 mt-3">{{ item.rtuId }}</h6>
                            <p class="text-center mb-0">{{ item.rtuName }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>