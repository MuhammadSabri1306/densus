<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@/components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";

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

const viewStore = useViewStore();
const isSaving = ref(false);

const onSave = schedule => {
    isSaving.value = true;
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
    <div v-if="show">
        <div v-if="!isLoading">
            <div class="py-4 d-flex justify-content-end">
                <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                    <VueFeather type="info" size="1em" />
                    <span class="ms-2">Keterangan Activity Categories</span>
                </button>
            </div>
            <Datatable @update="onSave" />
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
    </div>
</template>