<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@/components/DialogActivityCategory.vue";
import Datatable from "./Datatable.vue";
import ChartActivityOnMonth from "@/components/ChartActivityOnMonth.vue";
import InputSwitch from "primevue/inputswitch";

const show = ref(false);
const showCategory = ref(false);
const isLoading = ref(true);

const viewStore = useViewStore();
const activityStore = useActivityStore();

const isFetchSuccess = ref(false);
const excelDownloadUrl = computed(() => activityStore.performanceExcelExportUrl);

const dataTable = ref(null);
const fetch = async () => {
    show.value = true;
    isLoading.value = true;
    await nextTick();
    activityStore.getPerformance(({ success, data }) => {
        const tableData = {};
        if(data.category_list)
            tableData.categories = data.category_list;
        if(data.performance)
            tableData.performance = data.performance;
        if(data.month_list) {
            tableData.monthCol = data.month_list.map(monthNumber => {
                const currMonth = viewStore.monthList.find(item => item.number == monthNumber);
                return currMonth ? currMonth.name : monthNumber;
            });
        }
        if(success) {
            dataTable.value.setData(tableData);
            isFetchSuccess.value = true;
        }
        isLoading.value = false;
    });
};
// onMounted(() => console.log(dataTable.value));

defineExpose({ fetch });

const showTable = ref(true);
</script>
<template>
    <div v-if="show">
        <div>
            <div v-if="!isLoading" class="row align-items-center gy-4 py-4">
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
                    <a :href="excelDownloadUrl" target="_blank" class="btn-icon ms-auto">
                        <VueFeather type="download" size="1.2em" />
                        <span class="ms-1">Download</span>
                    </a>
                </div>
                <div class="col-auto ms-auto col-md-12 text-end col-lg-auto ms-lg-4">
                    <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                        <VueFeather type="info" size="1em" />
                        <span class="ms-2">Keterangan Activity Categories</span>
                    </button>
                </div>
            </div>
            <div v-show="showTable">
                <div v-if="isLoading" class="card">
                    <div class="card-body">
                        <div class="m-t-30">
                            <div v-for="x in 4" class="row mb-5">
                                <div v-for="x in 4" class="col"><Skeleton height="2rem" /></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="!isFetchSuccess" class="px-4 py-3 border">
                    <h4 class="text-center">Gagal memuat data.</h4>
                </div>
                <Datatable ref="dataTable" @showCategory="showCategory = true" />
            </div>
            <ChartActivityOnMonth v-if="!isLoading" v-show="!showTable" />
        </div>
        <DialogActivityCategory v-model:visible="showCategory" />
    </div>
</template>