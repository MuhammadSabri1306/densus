<script setup>
import { ref, computed, watch, reactive } from "vue";
import { useRoute } from "vue-router";
import { toRoman } from "@helpers/number-format";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import { categoryList } from "@/configs/gepee-evidence";
import { toFixedNumber } from "@helpers/number-format";
import { BuildingOffice2Icon } from "@heroicons/vue/24/solid";
import CheckedScoreGepeeEvidence from "@components/CheckedScoreGepeeEvidence.vue";
import Skeleton from "primevue/skeleton";
import StarScore from "@components/ui/StarScore.vue";

const emit = defineEmits(["fetched"]);
const info = reactive({
    location: null,
    count: [],
    score: 0
});

const categoryCount = computed(() => {
    const countVal = info.count;
    const data = [];
    categoryList.forEach(item => {

        if(countVal[item.code]) {
            const { category, checkedCount, targetCount } = countVal[item.code];
            data.push({ name: category, icon: item.icon, checkedCount, targetCount });
        }

    });
    return data;
});

const getCategoryDesc = (checkedCount, targetCount) => {
    let text = `Telah terisi ${ checkedCount }`;
    if(targetCount)
        text += `/${ targetCount }`;
    return text;
};

const route = useRoute();
const gepeeEvdStore = useGepeeEvdStore();
const isLoading = ref(true);

const watcherSrc = () => {
    const divre = route.params.divreCode;
    const witel = route.params.witelCode;
    
    if(divre)
        return { divre };
    else if(witel)
        return { witel };
    else return {};
};

const watcherCallback = settedFilter => {
    isLoading.value = true;
    gepeeEvdStore.getLocationInfo(settedFilter, data => {
        if(data.count)
            info.count = data.count;
        if(data.location)
            info.location = data.location;
        if(data.score)
            info.score = data.score;

        isLoading.value = false;
        emit("fetched", data);
    });
};

// watch(watcherSrc, watcherCallback);
const fetch = () => watcherCallback(watcherSrc());
defineExpose({ fetch });
fetch();

const locationName = computed(() => {
    const location = info.location;
    if(location && location.level == "nasional")
        return "NASIONAL";
    if(location && location.level == "divre")
        return location.divre_name;
    if(location && location.level == "witel")
        return location.witel_name;
    return null;
});

const semesterText = computed(() => {
    const semester = gepeeEvdStore.filters.semester;
    const year = gepeeEvdStore.filters.year;
    return `Semester ${ toRoman(semester) } ${ year }`;
});
</script>
<template>
    <div class="row dashboard-default-sec">
        <div class="col-md-6 col-lg-4">
            <div class="card income-card card-info-main card-primary card-h-full">                                 
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <div class="round-box">
                            <BuildingOffice2Icon style="enable-background:new 0 0 448.057 448.057;" />
                        </div>
                        <p class="mb-2">Gepee Evidence level</p>
                        <div v-if="!isLoading">
                            <h5>{{ locationName }}</h5>
                            <StarScore :score="info.score" class="tw-text-xl f-w-600" />
                        </div>
                        <div v-else class="d-flex flex-column align-items-center">
                            <Skeleton width="80%" class="mb-4" />
                            <Skeleton width="3rem" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-8">
            <div class="card card-body">
                <div class="card-header pb-0">
                    <div class="header-top">
                        <h5>Target Capaian</h5>
                        <span class="f-12">{{ semesterText }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="!isLoading" class="row top-sell-sec gy-5">
                        <div v-for="item in categoryCount" class="col-md-6 col-xl-3">
                            <div class="media">
                                <component :is="item.icon" class="tw-w-8 tw-h-8 me-2" />
                                <div class="media-body">
                                    <h6>{{ item.name }}</h6>
                                    <p>{{ getCategoryDesc(item.checkedCount, item.targetCount) }}</p>
                                    <CheckedScoreGepeeEvidence v-if="item.code != 'D'" :checkedCount="item.checkedCount" :targetCount="item.targetCount" />
                                    <CheckedScoreGepeeEvidence v-else :checkedCount="item.checkedCount" :targetCount="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="row top-sell-sec gy-5">
                        <div v-for="n in 4" class="col-md-6 col-xl-3">
                            <Skeleton height="103px" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>

.card-info-main .round-box {
    width: 5rem!important;
    height: 5rem!important;
}

.card-info-main svg {
    width: 3rem!important;
    height: 3rem!important;
}

</style>