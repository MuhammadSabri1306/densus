<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    data: { type: Object, default: {} }
});

const location = computed(() => props.data);
const route = useRoute();
const level = computed(() => {
    const rtuCode = route.params.rtuCode;
    const witelCode = route.params.witelCode;
    const divreCode = route.params.divreCode;
    return rtuCode ? "rtu"
        : witelCode ? "witel"
        : divreCode ? "divre"
        : "nasional";
});

const headingText = computed(() => {
    const levelVal = level.value;
    const loc = location.value;
    if(levelVal == "rtu")
        return loc.value?.rtu_name || null;
    if(levelVal == "witel")
        return loc.value?.witel_name || null;
    return null;
});

const titleFirstLine = computed(() => {
    const levelVal = level.value;
    const loc = location.value;
    if(levelVal == "rtu")
        return loc?.witel_name || null;
    if(levelVal == "witel")
        return loc?.divre_name || null;
    return null;
});

const titleSecondLine = computed(() => {
    const loc = location.value;
    return loc?.rtu_name || null;
});

const isLoaded = computed(() => {
    const loc = location.value;
    return loc && loc.divre_name ? true : false;
});
</script>
<template>
    <div class="card income-card card-primary">                                 
        <div class="card-body text-center">                                  
            <div class="round-box">
                <VueFeather type="zap" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
            </div>
            <div v-if="!isLoaded" class="d-flex flex-column justify-content-center">
                <Skeleton height="1.5rem" width="12rem" class="mb-2" />
                <Skeleton height="1rem" width="20rem" />
                <Skeleton height="1rem" width="16rem" />
            </div>
            <h5 v-else-if="level == 'nasional'">Nasional</h5>
            <div v-else class="d-flex flex-column justify-content-center">
                <h5 v-if="headingText">{{ headingText }}</h5>
                <p v-if="titleFirstLine" class="small mb-0">{{ titleFirstLine }}</p>
                <p v-if="titleSecondLine" class="small">{{ titleSecondLine }}</p>
            </div>
            <div class="parrten">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 448.057 448.057" style="enable-background:new 0 0 448.057 448.057;" xml:space="preserve">
                    <g><g><path d="M404.562,7.468c-0.021-0.017-0.041-0.034-0.062-0.051c-13.577-11.314-33.755-9.479-45.069,4.099 c-0.017,0.02-0.034,0.041-0.051,0.062l-135.36,162.56L88.66,11.577C77.35-2.031,57.149-3.894,43.54,7.417 c-13.608,11.311-15.471,31.512-4.16,45.12l129.6,155.52h-40.96c-17.673,0-32,14.327-32,32s14.327,32,32,32h64v144 c0,17.673,14.327,32,32,32c17.673,0,32-14.327,32-32v-180.48l152.64-183.04C419.974,38.96,418.139,18.782,404.562,7.468z"></path></g></g><g><g><path d="M320.02,208.057h-16c-17.673,0-32,14.327-32,32s14.327,32,32,32h16c17.673,0,32-14.327,32-32 S337.694,208.057,320.02,208.057z"></path></g></g>
                </svg>
            </div>
        </div>
    </div>
</template>