<script setup>
import { ref, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";
import SectionActivityExecDetail from "@/components/SectionActivityExecDetail.vue";

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const activityStore = useActivityStore();
const currSchedule = computed(() => activityStore.schedule.find(item => item.id == scheduleId.value)|| null);
const currDateString = () => new Intl.DateTimeFormat('id', { dateStyle: 'long'}).format() || null;

const currLocation = computed(() => {
    const location = activityStore.location;
    if(!currSchedule.value)
        return {};
    return location.find(item => item.id == currSchedule.value.id_lokasi)|| {};
});

const title = computed(() => {
    const category = activityStore.category.map((item, index) => {
        const no = index + 1;
        return { no, ...item };
    });
    if(!currSchedule.value)
        return "Daftar Aktifitas";

    const currCategory = category.find(item => item.id == currSchedule.value.id_category) || {};
    return `${ currCategory.no }. ${ currCategory.activity } (${ currCategory.alias })`;
});

const executionList = computed(() => activityStore.execution);
activityStore.fetchCategory();

const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    activityStore.fetchExecution(scheduleId.value, true, () => isLoading.value = false);
};
fetch();

const router = useRouter();
const currDetail = ref(null);

const showDialogList = ref(true);
const showDialogDetail = ref(false);

const dialog = {
    onListHide: () => {
        if(!showDialogDetail.value)
            router.push("/gepee");
    },
    onDetailHide: () => {
        currDetail.value = null;
        showDialogList.value = true;
    },
    showDetail: currItem => {
        currDetail.value = currItem;
        showDialogDetail.value = true;
        showDialogList.value = false;
    }
};
</script>
<template>
    <div>
        <Dialog header="Checklist Activity" v-model:visible="showDialogList" modal maximizable draggable @afterHide="dialog.onListHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ title }}</h6>
                            <p class="mb-0">Check Activity {{ currDateString() }}</p>
                            <p class="mb-0">{{ currLocation.sto_name }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="isLoading">
                    <Skeleton v-for="n in 4" class="block mb-3" />
                </div>
                <div v-else-if="executionList.length > 0">
                    <table class="table table-head-primary">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Judul Activity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in executionList">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.title }}</td>
                                <td>
                                    <b v-if="item.status == 'submitted'" class="text-capitalize text-muted">submitted</b>
                                    <span v-else-if="item.status == 'approved'">
                                        Telah di-<b class="text-capitalize font-primary">approve</b> oleh <b>{{ item.user_username }}/{{ item.user_name }}</b>
                                    </span>
                                    <span v-else>
                                        Telah di-<b class="text-capitalize font-danger">reject</b> oleh <b>{{ item.user_username }}/{{ item.user_name }}</b>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="dialog.showDetail(item)" class="btn btn-primary">Detail</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4">
                    <p class="text-center text-muted"><b>Belum ada Checklist Activity.</b></p>
                </div>
            </div>
        </Dialog>
        <Dialog header="Detail Activity" v-model:visible="showDialogDetail" modal maximizable draggable @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ title }}</h6>
                            <p class="mb-0">Check Activity {{ currDateString() }}</p>
                            <p class="mb-0">{{ currLocation.sto_name }}</p>
                        </div>
                    </div>
                </div>
                <SectionActivityExecDetail v-if="currDetail" :data="currDetail" @close="showDialogDetail = false" />
            </div>
        </Dialog>
    </div>
</template>