<script setup>
import { ref } from "vue";
import { useActivityStore } from "@stores/activity";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";
import DialogExportLinkVue from "@components/ui/DialogExportLink.vue";

const show = ref(false);
const isLoading = ref(true);
const showCategory = ref(false);

const activityStore = useActivityStore();
const fetch = () => {
    show.value = true;
    isLoading.value = true;
    activityStore.fetchLocation(true, () => isLoading.value = false);
};

defineExpose({ fetch });
const showDialogExport = ref(false);
</script>
<template>
    <div v-if="show">
        <div v-if="!isLoading">
            <div class="py-4 row g-4 justify-content-end">
                <div class="col-auto">
                    <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                        <VueFeather type="info" size="1em" />
                        <span class="ms-2">Keterangan Activity Categories</span>
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" @click="showDialogExport = true" class="btn btn-outline-info bg-white btn-icon px-3">
                        <VueFeather type="download" size="1em" />
                        <span class="ms-2">Export</span>
                    </button>
                </div>
            </div>
            <Datatable />
        </div>
        <div v-else class="card">
            <div class="card-body">
                <div class="m-t-30">
                    <div v-for="x in 4" class="row mb-5">
                        <div v-for="x in 4" class="col"><Skeleton height="2rem" /></div>
                    </div>
                </div>
            </div>
        </div>
        <DialogActivityCategory v-model:visible="showCategory" />
        <DialogExportLinkVue v-if="showDialogExport" baseUrl="/export/excel/activity/execution" title="Export Data Activity Pelaksanaan"
            useDivre useWitel useYear requireYear @close="showDialogExport = false" />
    </div>
</template>