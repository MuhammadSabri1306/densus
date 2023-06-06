<script setup>
import { ref } from "vue";
import { useActivityStore } from "@stores/activity";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

const showCategory = ref(false);

const activityStore = useActivityStore();
const isCategoryLoading = ref(true);
const isLocationLoading = ref(true);
const isFirstFetch = ref(true);

activityStore.fetchCategory(false, () => isCategoryLoading.value = false);
const fetch = () => {
    isLocationLoading.value = true;
    const forceFetch = !isFirstFetch.value;
    setTimeout(() => {
        activityStore.fetchLocation(forceFetch, () => isLocationLoading.value = false);
        isFirstFetch.value = false;
    }, 500);
};

defineExpose({ fetch });

const showDialogExport = ref(false);
</script>
<template>
    <div v-if="!isLocationLoading && !isCategoryLoading">
        <div class="py-4 d-flex justify-content-end">
            <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                <VueFeather type="info" size="1em" />
                <span class="ms-2">Keterangan Activity Categories</span>
            </button>
        </div>
        <Suspense>
            <Datatable @showCategory="showCategory = true" />
            <template #fallback>
                <div class="card">
                    <div class="card-body">
                        <div class="m-t-30">
                            <div v-for="x in 4" class="row mb-5">
                                <div v-for="x in 4" class="col"><Skeleton height="2rem" /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Suspense>
        <DialogActivityCategory v-model:visible="showCategory" />
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/activity/execution" title="Export Data Activity Pelaksanaan"
            useDivre useWitel useYear requireYear @close="showDialogExport = false" />
    </div>
</template>