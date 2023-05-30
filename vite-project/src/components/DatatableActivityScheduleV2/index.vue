<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useViewStore } from "@stores/view";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";

const showCategory = ref(false);

const activityStore = useActivityStore();
const isCategoryLoading = ref(true);
const isLocationLoading = ref(true);

activityStore.fetchCategory(false, () => isCategoryLoading.value = false);
const fetch = () => {
    isLocationLoading.value = true;
    setTimeout(() => {
        activityStore.fetchLocation(true, () => isLocationLoading.value = false);
    }, 500);
};

defineExpose({ fetch });

const viewStore = useViewStore();
const isSaving = ref(false);

const onSave = schedule => {
    isSaving.value = true;
    // activityStore.saveScheduleV2(schedule, response => {
    activityStore.saveSchedule(schedule, response => {
        isSaving.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Jadwal Activity", "Berhasil menyimpan jadwal bulan ini.", true);
        fetch();
    });
};
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
            <Datatable @update="onSave" @showCategory="showCategory = true" />
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
    </div>
</template>