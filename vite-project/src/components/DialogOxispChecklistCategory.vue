<script setup>
import { ref, computed } from "vue";
import { useOxispCheckStore } from "@/stores/oxisp-check";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";

defineEmits(["close"]);
const showDialog = ref(true);

const oxispCheckStore = useOxispCheckStore();
const categories = computed(() => oxispCheckStore.categories);
const isLoading = ref(true);

if(categories.value.length < 1) {
    oxispCheckStore.fetchCategory(false, () => {
        isLoading.value = false;
    });
} else {
    isLoading.value = false;
}
</script>
<template>
    <Dialog header="OX ISP Check Categories" v-model:visible="showDialog" :modal="true"
        class="dialog-basic" @after-hide="$emit('close')">
        <div class="px-4 py-4 py-md-0">
            <table class="table p-datatable-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="categories.length > 0">
                        <tr v-for="(item, index) in categories">
                            <td>{{ index + 1 }}</td>
                            <td class="tw-uppercase">{{ item.code }}</td>
                            <td>{{ item.title }}</td>
                            <td>{{ item.description }}</td>
                        </tr>
                    </template>
                    <template v-if="isLoading">
                        <tr v-for="i in 5">
                            <td colspan="4">
                                <div class="col-12"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </Dialog>
</template>