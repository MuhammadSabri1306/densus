<script setup>
import { ref, computed, watch } from "vue";
import { useViewStore, watchLoading } from "@stores/view";
import { useOxispCheckStore } from "@stores/oxisp-check";
import { isFileImg } from "@helpers/file";
import Dialog from "primevue/dialog";
import Image from "primevue/image";
import FormOxispChecklistAdd from "@components/FormOxispChecklistAdd.vue";
import FormOxispChecklistEdit from "@components/FormOxispChecklistEdit.vue";

const emit = defineEmits(["close", "update"]);
const props = defineProps({
    location: { type: Object, default: {} },
    checkData: { type: Object, default: {} },
});

const currLoc = computed(() => props.location);
const currCheck = ref({});

const isDialogInputShow = ref(false);
const isDialogDetailShow = ref(false);
const isDialogUpdateShow = ref(false);

const watcherSrc = () => props.checkData;
const watcherCall = checkData => {
    currCheck.value = checkData;
    if(!checkData.is_exists) {
        isDialogInputShow.value = true;
        isDialogDetailShow.value = false;
        isDialogUpdateShow.value = false;
    } else {
        isDialogInputShow.value = false;
        isDialogDetailShow.value = true;
        isDialogUpdateShow.value = false;
    }
};

watch(watcherSrc, watcherCall);
watcherCall(watcherSrc());

const roomTitle = computed(() => {
    const checkData = currCheck.value;
    if(checkData.room?.name)
        return checkData.room.name;
    return "OX ISP Checklist";
});

const stoTitle = computed(() => {
    const loc = currLoc.value;
    if(!loc?.sto_kode || !loc?.sto_name)
        return null;
    return loc.sto_kode + " / " + loc.sto_name;
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

const inputData = computed(() => {
    const location = props.location;
    const checkData = currCheck.value;
    return { location, ...checkData };
});

const onCheckCreate = () => {
    emit("update");
    isDialogInputShow.value = false;
};

const isUpdating = ref(false);
const showDialogUpdate = () => {
    isUpdating.value = true;
    isDialogDetailShow.value = false;
};

const onDialogDetailHide = () => {
    if(isUpdating.value)
        isDialogUpdateShow.value = true;
    else
        emit("close");
};

const onDialogUpdateHide = () => {
    isDialogDetailShow.value = true;
    isUpdating.value = false;
};

const oxispCheckStore = useOxispCheckStore();
const isLoading = ref(false);
watchLoading(isLoading);

const onCheckDelete = () => {
    const isConfirm = confirm("Anda akan menghapus item data OX ISP Checklist. Lanjutkan?");
    if(!isConfirm)
        return;
    isLoading.value = true;
    oxispCheckStore.delete(currCheck.value?.check_value.id, ({ success }) => {
        isLoading.value = false;
        if(success) {
            emit("update");
            isDialogDetailShow.value = false;
        }
    });
};

const onCheckUpdate = newCheckValue => {
    const check = currCheck.value;
    check.check_value = newCheckValue;
    currCheck.value = check;

    emit("update");
    isDialogUpdateShow.value = false;
};
</script>
<template>
    <Dialog header="Input Data OX ISP Checklist" v-model:visible="isDialogInputShow" :modal="true"
        class="dialog-basic" @afterHide="$emit('close')">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                <div class="row">
                    <div class="col-auto mt-1">
                        <VueFeather type="check-circle" class="tw-w-20 font-success" />
                    </div>
                    <div class="col">
                        <h6 class="m-b-5 font-success f-w-700">{{ roomTitle }}</h6>
                        <p class="mb-0 f-w-600">{{ stoTitle }}</p>
                        <p v-if="categoryTitle" class="mb-0">{{ categoryTitle }}</p>
                        <p class="mb-0">{{ monthTitle }}</p>
                    </div>
                </div>
            </div>
            <div v-if="isDialogInputShow" class="px-4">
                <FormOxispChecklistAdd :currCheck="inputData" @cancel="isDialogInputShow = false" @save="onCheckCreate" />
            </div>
        </div>
    </Dialog>
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
                        <p class="mb-0 f-w-600">{{ stoTitle }}</p>
                        <p v-if="categoryTitle" class="mb-0">{{ categoryTitle }}</p>
                        <p class="mb-0">{{ monthTitle }}</p>
                    </div>
                    <div v-if="checkStatus == 'submitted' || checkStatus == 'rejected'" class="col mt-auto mt-auto mb-3">
                        <div class="row flex-nowrap gx-4 gy-3 justify-content-end align-items-end">
                            <div class="col-auto">
                                <button type="button" @click="showDialogUpdate" class="btn btn-info">Update</button>
                            </div>
                            <div class="col-auto">
                                <button type="button" @click="onCheckDelete" class="btn btn-danger d-inline-flex">Hapus</button>
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
                        <td>{{ currCheck?.check_value.note || '-' }}</td>
                    </tr>
                    <tr>
                        <th class="lg:tw-whitespace-nowrap">Evidence</th>
                        <td>
                            <div v-if="isEvidenceImg" class="position-relative">
                                <span>{{ currCheck?.check_value.evidence }}</span>
                                <Image :src="currCheck?.check_value.evidence_url" alt="gambar evidence" preview class="img-stretched-link" />
                            </div>
                            <a v-else :href="currCheck?.check_value.evidence_url" target="_blank">{{ currCheck?.check_value.evidence }}</a>
                        </td>
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
    <Dialog header="Update Data OX ISP Checklist" v-model:visible="isDialogUpdateShow" :modal="true"
        class="dialog-basic" @afterHide="onDialogUpdateHide">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                <div class="row">
                    <div class="col-auto mt-1">
                        <VueFeather type="check-circle" class="tw-w-20 font-success" />
                    </div>
                    <div class="col">
                        <h6 class="m-b-5 font-success f-w-700">{{ roomTitle }}</h6>
                        <p class="mb-0 f-w-600">{{ stoTitle }}</p>
                        <p v-if="categoryTitle" class="mb-0">{{ categoryTitle }}</p>
                        <p class="mb-0">{{ monthTitle }}</p>
                    </div>
                </div>
            </div>
            <div v-if="isDialogUpdateShow" class="px-4">
                <FormOxispChecklistEdit :currCheck="inputData" @cancel="isDialogUpdateShow = false" @save="onCheckUpdate" />
            </div>
        </div>
    </Dialog>
</template>