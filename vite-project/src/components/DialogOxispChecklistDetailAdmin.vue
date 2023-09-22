<script setup>
import { ref, computed, watch } from "vue";
import { useViewStore, watchLoading } from "@stores/view";
import { useOxispCheckStore } from "@stores/oxisp-check";
import { isFileImg } from "@helpers/file";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form2";
import Dialog from "primevue/dialog";
import Image from "primevue/image";

const emit = defineEmits(["close", "update"]);
const props = defineProps({
    location: { type: Object, default: {} },
    checkData: { type: Object, default: {} },
});

const currLoc = computed(() => props.location);
const currCheck = ref({});
const checkId = computed(() => currCheck.value?.check_value.id);

const isDialogDetailShow = ref(true);
const isDialogRejectShow = ref(false);
const isRejecting = ref(false);

const watcherSrc = () => props.checkData;
const watcherCall = checkData => currCheck.value = checkData;
watch(watcherSrc, watcherCall);
watcherCall(watcherSrc());

const roomTitle = computed(() => {
    const checkData = currCheck.value;
    if(checkData.room?.name)
        return checkData.room.name;
    return "OX ISP Checklist";
});

const categoryTitle = computed(() => {
    const checkData = currCheck.value;
    if(checkData.category?.title && checkData.category?.code)
        return (`Kategori ${ checkData.category.title } (${ checkData.category.code })`).toUpperCase();
    return null;
});

const isEvidenceImg = computed(() => {
    const evd = currCheck.value?.check_value.evidence;
    if(!evd)
        return false;
    return isFileImg(evd);
});

const checkStatus = computed(() => currCheck.value?.check_value.status);

const viewStore = useViewStore();
const monthTitle = computed(() => {
    const monthNumber = currCheck.value.month;
    const monthName = monthNumber ? viewStore.monthList[monthNumber - 1]?.name : null;
    const year = viewStore.filters.year;
    return monthName ? `Bulan ${ monthName } Tahun ${ year }` : `Tahun ${ year }`;
});

const oxispCheckStore = useOxispCheckStore();
const isLoading = ref(false);
watchLoading(isLoading);

const onDialogDetailHide = () => {
    if(isRejecting.value)
        isDialogRejectShow.value = true;
    else
        emit("close");
};

const showDialogReject = () => {
    isRejecting.value = true;
    isDialogDetailShow.value = false;
};

const onDialogRejectHide = () => {
    isRejecting.value = false;
    isDialogDetailShow.value = true;
};

const onCheckValueUpdate = newCheckValue => {
    const check = currCheck.value;
    check.check_value = newCheckValue;
    currCheck.value = check;
    emit("update");
};

const onApprove = () => {
    const isConfirm = confirm("Anda akan melakukan Approve item data OX ISP Checklist. Lanjutkan?");
    if(!isConfirm)
        return;

    isLoading.value = true;
    oxispCheckStore.approve(checkId.value, response => {
        isLoading.value = false;
        if(response.success)
            onCheckValueUpdate(response.data.check_value);
    });
};

const { data, v$, hasSubmitted, getInvalidClass } = useDataForm({
    reject_description: { required }
});

const onReject = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;

    const isConfirm = confirm("Anda akan melakukan reject item data OX ISP Checklist. Lanjutkan?");
    if(!isConfirm) {
        hasSubmitted.value = false;
        return;
    }
    
    const body = { reject_description: data.reject_description };
    isLoading.value = true;

    oxispCheckStore.reject(checkId.value, body, response => {
        isLoading.value = false;
        if(response.success) {
            onCheckValueUpdate(response.data.check_value);
            isDialogRejectShow.value = false;
        }
    });
};
</script>
<template>
    <Dialog header="Detail Data OX ISP Checklist" v-model:visible="isDialogDetailShow" :modal="true"
        class="dialog-basic" @afterHide="onDialogDetailHide">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="card card-body bg-light text-dark p-t-25 p-l-30 p-r-30">
                <div class="row">
                    <div class="col-auto mt-1 mb-3">
                        <VueFeather type="check-circle" class="tw-w-20 font-success" />
                    </div>
                    <div class="col-auto mb-3">
                        <h6 class="m-b-5 font-success f-w-700">{{ roomTitle }}</h6>
                        <p class="mb-0 f-w-600">{{ currLoc.sto_name }}</p>
                        <p v-if="categoryTitle" class="mb-0">{{ categoryTitle }}</p>
                        <p class="mb-0">{{ monthTitle }}</p>
                    </div>
                    <div v-if="checkStatus !== null && checkStatus == 'submitted'" class="col mt-auto mt-auto mb-3">
                        <div class="row flex-nowrap gx-4 gy-3 justify-content-end align-items-end">
                            <div class="col-auto">
                                <button type="button" @click="onApprove" class="btn btn-success">Approve</button>
                            </div>
                            <div class="col-auto">
                                <button type="button" @click="showDialogReject" class="btn btn-danger d-inline-flex">Reject</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-lg-4">
                <table class="table text-muted"><tbody>
                    <tr>
                        <th class="lg:tw-w-[1px] lg:tw-whitespace-nowrap">Status</th>
                        <td v-if="checkStatus == 'submitted'" class="f-w-700">Submitted</td>
                        <td v-else-if="checkStatus == 'approved'" class="f-w-700 text-success">Approved</td>
                        <td v-else-if="checkStatus == 'rejected'" class="f-w-700 text-danger">Rejected</td>
                        <td v-else class="f-w-700">-</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Ketersediaan Ruangan</th>
                        <td>{{ currCheck?.check_value.is_room_exists == 1 ? 'Sudah Ada' : 'Belum Ada' }}</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Kondisi</th>
                        <td>{{ currCheck?.check_value.is_ok == 1 ? 'OK' : 'Not OK' }}</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Catatan</th>
                        <td v-if="currCheck?.check_value.note">{{ currCheck.check_value.note }}</td>
                        <td v-else class="f-w-700">-</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Evidence</th>
                        <td v-if="currCheck?.check_value.evidence">
                            <div v-if="isEvidenceImg" class="position-relative">
                                <span>{{ currCheck?.check_value.evidence }}</span>
                                <Image :src="currCheck?.check_value.evidence_url" alt="gambar evidence" preview class="img-stretched-link" />
                            </div>
                            <a v-else :href="currCheck?.check_value.evidence_url" target="_blank">{{ currCheck?.check_value.evidence }}</a>
                        </td>
                        <td v-else class="f-w-700">-</td>
                    </tr>
                    <tr v-if="currCheck?.check_value.status == 'rejected'">
                        <th class="lg:tw-whitespace-nowrap">Alasan Reject</th>
                        <td>{{ currCheck?.check_value.reject_description }}</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Terakhir diupdate oleh</th>
                        <td>{{ currCheck?.check_value.user_name }} ({{ currCheck?.check_value.user_name }})</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Terakhir diupdate pada</th>
                        <td>{{ currCheck?.check_value.updated_at }}</td>
                    </tr>
                </tbody></table>
            </div>
            <div class="px-4 px-md-5 pt-4 d-flex">
                <button type="button" @click="isDialogDetailShow = false" class="btn btn-secondary ms-auto">Tutup</button>
            </div>
        </div>
    </Dialog>
    <Dialog header="Update Data OX ISP Checklist" v-model:visible="isDialogRejectShow" :modal="true"
        class="dialog-basic" @afterHide="onDialogRejectHide">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="card card-body bg-light text-dark p-t-25 p-l-30 p-r-30">
                <div class="row">
                    <div class="col-auto mt-1 mb-3">
                        <VueFeather type="check-circle" class="tw-w-20 font-success" />
                    </div>
                    <div class="col-auto mb-3">
                        <h6 class="m-b-5 font-success f-w-700">{{ roomTitle }}</h6>
                        <p class="mb-0 f-w-600">{{ currLoc.sto_name }}</p>
                        <p v-if="categoryTitle" class="mb-0">{{ categoryTitle }}</p>
                        <p class="mb-0">{{ monthTitle }}</p>
                    </div>
                </div>
            </div>
            <div v-if="isDialogRejectShow" class="px-4">
                <form @submit.prevent="onReject">
                    <div class="mb-5">
                        <label for="txtRejectDesc" class="required">Deskripsi Reject</label>
                        <textarea v-model="data.reject_description" :class="getInvalidClass('reject_description', 'is-invalid')"
                            id="txtRejectDesc" rows="5" class="form-control" autofocus></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <button type="button" @click="isDialogRejectShow = false" :disabled="isLoading"
                            class="btn btn-secondary">Batalkan</button>
                        <button type="submit" :class="{ 'btn-loading': isLoading }" :disabled="isLoading"
                            class="btn btn-lg btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </Dialog>
</template>