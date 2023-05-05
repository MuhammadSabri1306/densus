<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import { categoryList } from "@/configs/gepee-evidence";

const dataCategory = ref([]);
const splitCategoryItem = (category, checker) => {
    const data = categoryList
        .filter((item, index) => checker(index))
        .map(item => {
            if(category[item.code])
                item.evidence = category[item.code];
            return item;
        });
    return data;
};

const categoryItemOdd = computed(() => {
    const category = dataCategory.value;
    return splitCategoryItem(category, index => index % 2 == 0);
});

const categoryItemEven = computed(() => {
    const category = dataCategory.value;
    return splitCategoryItem(category, index => index % 2 != 0);
});

const route = useRoute();
const gepeeEvdStore = useGepeeEvdStore();
const isLoading = ref(true);

const watcherSrc = () => route.params.witelCode;
const watcherCallback = witel => {
    isLoading.value = true;
    gepeeEvdStore.getCategoryData(witel, data => {
        if(data.category_data)
            dataCategory.value = data.category_data;
        isLoading.value = false;
    });
};

watch(watcherSrc, watcherCallback);
const fetch = () => watcherCallback(watcherSrc());
defineExpose({ fetch });
watcherCallback(watcherSrc());

const witel = computed(() => route.params.witelCode);
const getCategoryRoute = idCategory => `/gepee-evidence/witel/${ witel.value }/${ idCategory }`;
</script>
<template>
    <div class="row">
        <div class="col-md-6">
            <div v-for="category in categoryItemOdd" class="card">
                <div class="card-header bg-primary d-flex align-items-center">
                    <component :is="category.iconSolid" class="tw-w-6 tw-h-6 me-2" />
                    <h5 class="card-title">{{ category.name }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody v-if="!isLoading">
                            <tr v-for="item in category.evidence">
                                <td>
                                    {{ item.sub_category }}<br>
                                    <b>Jumlah item: {{ item.data_count }}</b>
                                </td>
                                <td class="text-right">
                                    <span>
                                        <RouterLink :to="getCategoryRoute(item.id_category)" class="btn btn-primary">Detail</RouterLink>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div v-for="category in categoryItemEven" class="card">
                <div class="card-header bg-primary d-flex align-items-center">
                    <component :is="category.iconSolid" class="tw-w-6 tw-h-6 me-2" />
                    <h5 class="card-title">{{ category.name }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody v-if="!isLoading">
                            <tr v-for="item in category.evidence">
                                <td>
                                    {{ item.sub_category }}<br>
                                    <b>Jumlah item: {{ item.data_count }}</b>
                                </td>
                                <td class="text-right">
                                    <span>
                                        <RouterLink :to="getCategoryRoute(item.id_category)" class="btn btn-primary">Detail</RouterLink>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>