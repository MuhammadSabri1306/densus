<script setup>
import { ref, computed, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useOxispStore } from "@/stores/oxisp";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { isFileImg } from "@/helpers/file";
import Dialog from "primevue/dialog";
import ButtonGroupAction from "@/components/ButtonGroupAction.vue";
import Skeleton from "primevue/skeleton";
import FormOxispAdd from "@/components/FormOxispAdd.vue";
import FormOxispEditVue from "@/components/FormOxispEdit.vue";
import Image from "primevue/image";
import { DocumentIcon } from "@heroicons/vue/24/solid";

const emit = defineEmits(["update", "loaded"]);

const route = useRoute();
const year = computed(() => route.params.year);
const month = computed(() => route.params.month);
const idLocation = computed(() => route.params.idLocation);

const oxispStore = useOxispStore();
const currLocation = ref({});
const activityList = ref([]);

const viewStore = useViewStore();
const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    oxispStore.getList(year.value, month.value, idLocation.value, ({ data }) => {

        if(data.activity)
            activityList.value = data.activity;
        if(data.location)
            currLocation.value = data.location;

        isLoading.value = false;
    });
};
fetch();

const currUpdate = ref(null);
const currDetail = ref(null);

const resetReactiveData = () => {
    currUpdate.value = null;
    currDetail.value = null;
};

const router = useRouter();

const showDialogList = ref(true);
const showDialogDetail = ref(false);
const showRejectSection = ref(false);

const dialog = {
    onListHide: () => {
        if(!showDialogDetail.value)
            router.push("/oxisp/activity");
    },
    onDetailHide: () => {
        resetReactiveData();
        showDialogList.value = true;
    },
    showDetail: currItem => {
        currDetail.value = currItem;
        showDialogDetail.value = true;
        showDialogList.value = false;
        console.log(currDetail.value);
    }
};

const onApproved = () => {
    if(!confirm("Anda akan mengganti status menjadi Approved. Lanjutkan?"))
        return false;
    
    const oxispId = currDetail.value.id;
    oxispStore.approve(oxispId, response => {
        if(!response.success)
            return;
        viewStore.showToast("Approve Activity", "Berhasil melakukan Approve pada OXISP Activity", true);
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
    const oxispId = currDetail.value.id;
    isRejectSubmitted.value = true;
    if(!rejectDesc.value || rejectDesc.value.length < 1)
        return;
    if(!confirm("Anda akan mengganti status menjadi Rejected. Lanjutkan?"))
        return false;

    oxispStore.reject(oxispId, rejectDesc.value, response => {
        if(!response.success)
            return;
        viewStore.showToast("Reject Activity", "Berhasil melakukan Reject pada OXISP Activity", true);
        showDialogDetail.value = false;
        fetch();
        hideRejectSection();
        emit("update");
    });
};

const isCurrDetailImg = computed(() => {
    const currDetailData = currDetail.value;
    if(!currDetailData || !currDetailData.evidence)
        return false;
    return isFileImg(currDetailData.evidence);
});

const dialogProps = {
    class: "dialog-basic",
    modal: true,
    maximizable: true,
    draggable: true
};

const dialogTitle = computed(() => {
    return `<h6 class="m-b-5 font-success f-w-700">Check Activity OXISP</h6>
        <p class="mb-0">Bulan ${ month.value } Tahun ${ year.value }</p>
        <p class="mb-0">${ currLocation.value.sto_kode } / ${ currLocation.value.sto_name }</p>`;
})

const getStatusClass = status => {
    if(status == "submitted")
        return "tw-text-base badge rounded-pill badge-info text-white";
    if(status == "rejected")
        return "tw-text-base badge rounded-pill badge-danger text-light";
    return "tw-text-base badge rounded-pill badge-success text-light";
};
</script>
<template>
    <div>
        <Dialog header="Checklist Activity" v-model:visible="showDialogList" v-bind="dialogProps"
            @afterHide="dialog.onListHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col" v-html="dialogTitle"></div>
                    </div>
                </div>
                <div v-if="isLoading">
                    <Skeleton v-for="n in 4" class="block mb-3" />
                </div>
                <div v-else-if="activityList.length > 0" class="table-responsive">
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
                            <tr v-for="(item, index) in activityList">
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
                                <td>
                                    <button type="button" @click="dialog.showDetail(item)" class="btn btn-primary">Detail</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4">
                    <p class="text-center text-muted"><b>Belum ada Checklist.</b></p>
                </div>
            </div>
        </Dialog>
        <Dialog header="Detail Activity" v-model:visible="showDialogDetail" v-bind="dialogProps"
            @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col" v-html="dialogTitle"></div>
                    </div>
                </div>
                <div class="px-4">
                    <h6>Judul Activity</h6>
                    <p class="text-muted px-4">{{ currDetail.title }}</p>
                    <h6>Deskripsi</h6>
                    <p class="text-muted px-4">{{ currDetail.descr }}</p>
                    <h6>Before</h6>
                    <p class="text-muted px-4">{{ currDetail.descr_before }}</p>
                    <h6>After</h6>
                    <p class="text-muted px-4">{{ currDetail.descr_after }}</p>
                    <h6>File Evidence</h6>
                    <div v-if="isCurrDetailImg" class="d-flex flex-column align-items-center px-4 mb-5">
                        <div class="position-relative mb-2">
                            <img :src="currDetail.evidence_url" alt="gambar evidence" class="img-fluid" role="presentation" />
                            <Image :src="currDetail.evidence_url" :alt="currDetail.evidence" preview class="img-stretched-link" />
                        </div>
                        <span class="text-muted">{{ currDetail.evidence }}</span>
                    </div>
                    <div v-else class="px-4 py-2 mb-5">
                        <div class="border position-relative">
                            <div class="m-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <DocumentIcon class="tw-w-8 tw-h-8 text-muted" />
                                    </div>
                                    <div class="col position-static">
                                        <a :href="currDetail.evidence_url" target="_blank" class="stretched-link">{{ currDetail.evidence }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row gy-3 align-items-center mb-4">
                        <div class="col-auto">
                            <h6 class="mb-0">Status</h6>
                        </div>
                        <div class="col-auto">
                            <b :class="getStatusClass(currDetail.status)" class="text-capitalize">{{ currDetail.status }}</b>
                        </div>
                    </div>
                    <h6 v-if="currDetail.status == 'rejected'">Alasan Reject</h6>
                    <p v-if="currDetail.status == 'rejected'" class="text-muted px-4">{{ currDetail.reject_descr }}</p>
                    <h6>Terakhir diupdate oleh</h6>
                    <p class="text-muted px-4">{{ currDetail.user_name }} ({{ currDetail.user_name }})</p>
                    <h6>Terakhir diupdate pada</h6>
                    <p class="text-muted px-4 mb-5">{{ currDetail.updated_at }}</p>
                </div>
                <div v-if="showRejectSection" class="mb-5">
                    <form @submit.prevent="onReject">
                        <div class="mb-5">
                            <label for="textRejectDesc">Alasan Reject<span class="text-danger"> *</span></label>
                            <textarea v-model="rejectDesc" class="form-control" rows="5"
                                :class="{ 'is-invalid': (isRejectSubmitted && !rejectDesc) || isRejectSubmitted && rejectDesc.length < 1 }"></textarea>
                        </div>
                        <div class="row gx-5 gy-3 align-items-end">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-lg btn-danger">Reject</button>
                            </div>
                            <div class="col-auto">
                                <button type="button" @click="hideRejectSection" class="btn btn-light">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div v-else-if="currDetail.status == 'submitted'" class="mb-5 card card-body">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <button type="button" @click="onApproved" class="btn btn-primary">
                                <VueFeather type="check" size="1.2em" class="middle" />
                                <span class="ms-1 middle">Approve</span>
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" @click="showRejectSection = true" class="btn btn-danger">
                                <VueFeather type="x" size="1.2em" class="middle" />
                                <span class="ms-1 middle">Reject</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" @click="showDialogDetail = false" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </Dialog>
    </div>
</template>