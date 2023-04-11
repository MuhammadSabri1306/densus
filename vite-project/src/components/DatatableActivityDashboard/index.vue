<script setup>
import { ref } from "vue";
import { useActivityStore } from "@stores/activity";
import { useViewStore } from "@stores/view";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";
import ChartActivityOnMonth from "@components/ChartActivityOnMonth.vue";
import InputSwitch from "primevue/inputswitch";

const show = ref(false);
const showCategory = ref(false);
const isLoading = ref(true);

const monthList = ref([]);
const categoryList = ref([]);
const performance = ref([]);

const viewStore = useViewStore();
const monthListView = viewStore.monthList;

const activityStore = useActivityStore();
const isFetchSuccess = ref(false);
const fetch = () => {
    show.value = true;
    isLoading.value = true;
    activityStore.getPerformance(({ success, data }) => {
        categoryList.value = data.category_list;
        performance.value = data.performance;

        monthList.value = data.month_list.map(monthNumber => {
            const currMonth = monthListView.find(item => item.number == monthNumber);
            return currMonth ? currMonth.name : monthNumber;
        });

        isLoading.value = false;
        isFetchSuccess.value = success;
    });
};

defineExpose({ fetch });

const showTable = ref(true);
</script>
<template>
    <div v-if="show">
        <div v-if="!isLoading">
            <div class="row align-items-center py-4">
                <div class="col-auto">
                    <label for="showTable">Diagram Konsistensi</label>
                </div>
                <div class="col-auto">
                    <InputSwitch v-model="showTable" inputId="showTable" />
                </div>
                <div class="col-auto">
                    <label for="showTable">Tabel Activity</label>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                        <VueFeather type="info" size="1em" />
                        <span class="ms-2">Keterangan Activity Categories</span>
                    </button>
                </div>
            </div>
            <div v-show="showTable">
                <div v-if="isLoading" class="row">
                    <div v-for="n in 12" class="col-4 col-lg-3"></div>
                </div>
                <Datatable v-else-if="performance.length > 0" :performance="performance" :monthCol="monthList" :categories="categoryList" />
                <div v-else class="px-4 py-3 border">
                    <h4 class="text-center">{{ isFetchSuccess ? 'Belum ada' : 'Gagal memuat' }} data.</h4>
                </div>
            </div>
            <ChartActivityOnMonth v-show="!showTable" />
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