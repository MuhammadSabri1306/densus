<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@/stores/activity";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";

const activityStore = useActivityStore();
const isLoading = ref(true);

const activityCategory = computed(() => activityStore.category);

activityStore.fetchCategory(false, () => {
    isLoading.value = false;
});
</script>
<template>
    <Dialog header="Activity Categories" :modal="true" class="dialog-basic">
        <div class="px-4 py-4 py-md-0">
            <table class="table p-datatable-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Code</th>
                        <th>Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="activityCategory.length > 0">
                        <tr v-for="(item, index) in activityCategory">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.alias }}</td>
                            <td>{{ item.activity }}</td>
                        </tr>
                    </template>
                    <template v-if="isLoading">
                        <tr>
                            <td colspan="3">
                                <div class="col-12"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-9 col-md-7"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-10 col-md-8"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-7 col-md-5"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-6 col-md-4"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-10 col-md-8"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-6 col-md-4"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="col-9 col-md-7"><Skeleton width="100%" class="mb-3" borderRadius="16px"></Skeleton></div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </Dialog>
</template>