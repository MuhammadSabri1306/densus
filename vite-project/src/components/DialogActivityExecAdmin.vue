<script setup>
import { ref, computed, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useActivityStore } from "@stores/activity";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";
import SectionActivityExecDetail from "@components/SectionActivityExecDetail.vue";

const emit = defineEmits(["update", "loaded"]);

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const activityStore = useActivityStore();
const currSchedule = ref(null);
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

const executionList = ref([]);
activityStore.fetchCategory();

const viewStore = useViewStore();
const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    activityStore.getExecution(scheduleId.value, ({ data }) => {

        executionList.value = data.executionList;
        currSchedule.value = data.schedule;

        if(data.schedule) {
            viewStore.setFilter({
                divre: data.schedule.divre_kode,
                witel: data.schedule.witel_kode
            });
            nextTick(() => emit("loaded"));
        }

        isLoading.value = false;
    });
};
fetch();

const router = useRouter();
const currDetail = ref(null);

const showDialogList = ref(true);
const showRejectSection = ref(false);
const showDialogDetail = ref(false);
const showDialogEvidence = ref(false);

const dialog = {
    onListHide: () => {
        if(!showDialogDetail.value)
            router.push("/gepee/exec");
    },
    onDetailHide: () => {
        currDetail.value = null;
        showDialogList.value = true;
    },
    showDetail: currItem => {
        currDetail.value = currItem;
        showDialogDetail.value = true;
        showDialogList.value = false;
    },
    onEvidenceHide: () => {
        showDialogDetail.value = true;
        showDialogEvidence.value = false;
    },
    showEvidence: () => {
        showDialogEvidence.value = true;
        showDialogDetail.value = false;
    }
};

const onApproved = () => {
    if(!confirm("Anda akan mengganti status menjadi Approved. Lanjutkan?"))
        return false;
    
    const execId = currDetail.value.id;
    activityStore.approveExecution(execId, response => {
        if(!response.success)
            return;
        viewStore.showToast("Approve Activity", "Berhasil melakukan Approve pada Activity", true);
        showDialogDetail.value = false;
        fetch();
        emit("update");
    });
};

const rejectDesc = ref(null);
const isRejectSubmitted = ref(false);

const hideRejectSection = () => {
    showRejectSection.value = false;
    rejectDesc.value = null;
    isRejectSubmitted.value = false;
};

const onReject = () => {
    const execId = currDetail.value.id;
    isRejectSubmitted.value = true;
    if(!rejectDesc.value || rejectDesc.value.length < 1)
        return;
    if(!confirm("Anda akan mengganti status menjadi Rejected. Lanjutkan?"))
        return false;

    activityStore.rejectExecution(execId, rejectDesc.value, response => {
        if(!response.success)
            return;
        viewStore.showToast("Reject Activity", "Berhasil melakukan Reject pada Activity", true);
        showDialogDetail.value = false;
        fetch();
        hideRejectSection();
        emit("update");
    });
};
</script>
<template>
    <div>
        <Dialog header="Checklist Activity" v-model:visible="showDialogList" modal
            maximizable draggable @afterHide="dialog.onListHide" class="dialog-basic">
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
                                        <b class="text-capitalize font-primary">approve</b> oleh <b>{{ item.user_username }}/{{ item.user_name }}</b>
                                    </span>
                                    <span v-else>
                                        <b class="text-capitalize font-danger">reject</b> oleh <b>{{ item.user_username }}/{{ item.user_name }}</b>
                                    </span>
                                </td>
                                <td>
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
                <SectionActivityExecDetail v-if="currDetail" :data="currDetail" :showCloseButton="false" />
                <div v-if="!showRejectSection && currDetail" class="row mt-5 align-items-end">
                    <div v-if="currDetail.status == 'submitted'" class="col-7 col-md-auto ms-auto ms-md-0 mt-3">
                        <button type="button" @click="onApproved" class="btn btn-lg btn-primary">
                            <VueFeather type="check" size="1.2em" class="middle" />
                            <span class="ms-1 middle">Approve</span>
                        </button>
                    </div>
                    <div v-if="currDetail.status == 'submitted'" class="col-7 col-md-auto ms-auto ms-md-4 mt-3">
                        <button type="button" @click="showRejectSection = true" class="btn btn-lg btn-danger">
                            <VueFeather type="x" size="1.2em" class="middle" />
                            <span class="ms-1 middle">Reject</span>
                        </button>
                    </div>
                    <div class="col-7 col-md-auto ms-auto mt-4">
                        <button type="button" @click="showDialogDetail = false" class="btn btn-light">Cancel</button>
                    </div>
                </div>
                <form v-if="showRejectSection && currDetail" @submit.prevent="onReject" class="mt-5">
                    <div class="mb-5">
                        <label for="textRejectDesc">Alasan Reject<span class="text-danger"> *</span></label>
                        <textarea v-model="rejectDesc" :class="{ 'is-invalid': (isRejectSubmitted && !rejectDesc) || isRejectSubmitted && rejectDesc.length < 1 }" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <button type="button" @click="hideRejectSection" class="btn btn-light">Cancel</button>
                        <button type="submit" class="btn btn-lg btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </Dialog>
    </div>
</template>