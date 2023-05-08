<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import { BuildingOfficeIcon } from "@heroicons/vue/24/solid";
import Skeleton from "primevue/skeleton";
import StarScore from "@components/ui/StarScore.vue";

const route = useRoute();
const gepeeEvdStore = useGepeeEvdStore();
const isLoading = ref(false);

const list = ref([]);
const gridItems = computed(() => {
    return list.value.map(item => {
        const title = item.id_location ? item.witel_name : item.divre_name;
        const routeTo = item.witel_kode ? "/gepee-evidence/witel/" + item.witel_kode : "/gepee-evidence/divre/" + item.divre_kode;
        return { ...item, title, routeTo };
    });
});

const onFetched = data => {
    isLoading.value = false;
    if(data.location)
        list.value = data.location;
};

const divreCode = computed(() => route.params.divreCode);
const fetch = () => {
    isLoading.value = true;
    if(divreCode.value)
        gepeeEvdStore.getWitelList(divreCode.value, onFetched);
    else
        gepeeEvdStore.getDivreList(onFetched);
};

// watch(() => route.params.divreCode, fetch);
defineExpose({ fetch });
fetch();
</script>
<template>
    <div class="top-dealer-sec">
        <div v-if="isLoading" class="row">
            <div v-for="n in 12" class="col-md-3">
                <div class="card">
                    <div class="top-dealerbox d-flex flex-column align-items-center">
                        <div class="tw-w-12 tw-h-12 tw-bg-gray-200 rounded-circle d-flex mb-4">
                            <BuildingOfficeIcon class="tw-w-8 tw-h-8 m-auto tw-text-[#24695c]" />
                        </div>
                        <Skeleton width="80%" class="mb-3" />
                        <Skeleton width="2rem" class="mb-3" />
                        <Skeleton width="40%" />
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="row">
            <div v-for="(item, index) in gridItems" class="col-md-3">
                <div class="card card-h-full pt-3 pb-2">
                    <div class="top-dealerbox text-center">
                        <div class="d-flex justify-content-center mb-4">
                            <div class="tw-w-12 tw-h-12 tw-bg-gray-200 rounded-circle d-flex">
                                <BuildingOfficeIcon class="tw-w-8 tw-h-8 m-auto tw-text-[#24695c]" />
                            </div>
                        </div>
                        <h6>{{ item.title }}</h6>
                        <StarScore :key="index+1" :score="item.scores" class="f-w-600 mb-4" />
                        <RouterLink :to="item.routeTo" class="btn btn-rounded stretched-link">Detail</RouterLink>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>