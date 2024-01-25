<script setup>
import { computed } from "vue";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    isLoading: { type: Boolean, default: false },
    locs: { required: true }
});

const isLoading = computed(() => props.isLoading);
const locs = computed(() => props.locs);

const isLevelNasional = computed(() => {
    const data = locs.value;
    if(data.rtu_name)
        return false;
    if(data.witel_name)
        return false;
    if(data.divre_name)
        return false;
    return true;
});

const headingText = computed(() => {
    const data = locs.value;
    if(data.rtu_name)
        return data.rtu_name;
    if(data.witel_name)
        return data.witel_name;
    if(data.divre_name)
        return data.divre_name;
    return null;
});

const titleFirstLine = computed(() => {
    const data = locs.value;
    if(data.rtu_name)
        return data.witel_name;
    if(data.witel_name)
        return data.divre_name;
    return null;
});

const titleSecondLine = computed(() => {
    const data = locs.value;
    if(data.rtu_name)
        return data.divre_name;
    return null;
});
</script>
<template>
    <div class="card income-card card-primary">                                 
        <div class="card-body text-center">                                  
            <div class="round-box">
                <VueFeather type="zap" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
            </div>
            <div v-if="isLoading" class="tw-flex tw-flex-col tw-gap-4">
                <Skeleton height="1.7rem" class="tw-w-full tw-max-w-[12rem]" />
                <Skeleton height="1.2rem" class="tw-w-full tw-max-w-[20rem]" />
                <Skeleton height="1.2rem" class="tw-w-full tw-max-w-[16rem]" />
            </div>
            <h5 v-else-if="isLevelNasional">Nasional</h5>
            <div v-else class="d-flex flex-column justify-content-center">
                <h5 v-if="headingText">{{ headingText }}</h5>
                <p v-if="titleFirstLine" class="small !tw-font-semibold mb-0">{{ titleFirstLine }}</p>
                <p v-if="titleSecondLine" class="small !tw-font-semibold">{{ titleSecondLine }}</p>
            </div>
            <div class="parrten">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 448.057 448.057" style="enable-background:new 0 0 448.057 448.057;" xml:space="preserve">
                    <g><g><path d="M404.562,7.468c-0.021-0.017-0.041-0.034-0.062-0.051c-13.577-11.314-33.755-9.479-45.069,4.099 c-0.017,0.02-0.034,0.041-0.051,0.062l-135.36,162.56L88.66,11.577C77.35-2.031,57.149-3.894,43.54,7.417 c-13.608,11.311-15.471,31.512-4.16,45.12l129.6,155.52h-40.96c-17.673,0-32,14.327-32,32s14.327,32,32,32h64v144 c0,17.673,14.327,32,32,32c17.673,0,32-14.327,32-32v-180.48l152.64-183.04C419.974,38.96,418.139,18.782,404.562,7.468z"></path></g></g><g><g><path d="M320.02,208.057h-16c-17.673,0-32,14.327-32,32s14.327,32,32,32h16c17.673,0,32-14.327,32-32 S337.694,208.057,320.02,208.057z"></path></g></g>
                </svg>
            </div>
        </div>
    </div>
</template>