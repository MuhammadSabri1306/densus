<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { limitWords } from "@helpers/text-format";
import { toRoman } from "@helpers/number-format";
import { isFileImg } from "@helpers/file";
import Dialog from "primevue/dialog";
import ButtonGroupAction from "@components/ButtonGroupAction.vue";
import Skeleton from "primevue/skeleton";
import FormGepeeEvidence from "@components/FormGepeeEvidence.vue";
import Image from "primevue/image";
import { DocumentIcon } from "@heroicons/vue/24/solid";

const emit = defineEmits(["updated"]);

const route = useRoute();
const witelCode = computed(() => route.params.witelCode);
const idCategory = computed(() => route.params.idCategory);

const gepeeEvdStore = useGepeeEvdStore();
const isLoading = ref(true);

const evdList = ref([]);
const category = ref(null);
const location = ref(null);
const currDetail = ref(null);
const currUpdate = ref(null);

const categoryHeading = computed(() => category.value ? "Kategori " + category.value.category : "Gepee Evidence");
const categorySubHeading = computed(() => category.value ? category.value.sub_category : "Daftar Gepee Evidence");
const witelName = computed(() => location.value ? location.value.witel_name : null);
const locationId = computed(() => location.value ? Number(location.value.id_location) : 0);
const dayTitle = computed(() => {
    const filters = gepeeEvdStore.filters;
    const text = [];
    if(filters.semester)
        text.push(`Semester ${ toRoman(filters.semester) }`);
    if(filters.year)
        text.push(`Tahun ${ filters.year }`);
    return text.join(" ");
});

const resetReactiveData = () => {
    currDetail.value = null;
    currUpdate.value = null;
};

const fetchList = () => {
    gepeeEvdStore.getEvidenceList(witelCode.value, idCategory.value, data => {
        isLoading.value = false;
        if(data.evidence)
            evdList.value = data.evidence;
        if(data.location)
            location.value = data.location;
        if(data.category)
            category.value = data.category;
        console.log(data.location)
    });
};
fetchList();

const router = useRouter();
const showDialogList = ref(true);
const showDialogDetail = ref(false);
const showDialogAdd = ref(false);
const showDialogEdit = ref(false);

const viewStore = useViewStore();
const dialog = {
    onListHide: () => {
        const isOpenOtherDialog = showDialogDetail.value || showDialogAdd.value || showDialogEdit.value;
        if(!isOpenOtherDialog)
            router.push("/gepee-evidence/witel/" + witelCode.value);
    },
    onDetailHide: () => {
        resetReactiveData();
        showDialogList.value = true;
    },
    onFormAddHide: () => {
        dialog.onDetailHide();
    },
    onFormEditHide: () => {
        dialog.onDetailHide();
    },
    showDetail: currItem => {
        currDetail.value = currItem;
        showDialogDetail.value = true;
        showDialogList.value = false;
    },
    showFormAdd: () => {
        showDialogAdd.value = true;
        showDialogList.value = false;
    },
    showFormEdit: currItem => {
        currUpdate.value = currItem;
        showDialogEdit.value = true;
        showDialogList.value = false;
    },
    onFormAddSave: () => {
        viewStore.showToast("Input Gepee Evidence", "Berhasil menyimpan Gepee Evidence baru.", true);
        fetchList();
        showDialogAdd.value = false;
        emit("updated");
    },
    onFormEditSave: () => {
        viewStore.showToast("Update Gepee Evidence", "Berhasil menyimpan Gepee Evidence.", true);
        fetchList();
        showDialogEdit.value = false;
        emit("updated");
    }
};

const onDelete = evidenceId => {
    if(!confirm("Anda akan menghapus Gepee Evidence. Lanjutkan?"))
        return false;
    
    gepeeEvdStore.delete(evidenceId, response => {
        if(!response.success)
            return;
        
        viewStore.showToast("Hapus Gepee Evidence", "Berhasil menghapus Gepee Evidence.", true);
        fetchList();
        resetReactiveData();
        emit("updated");
    });
};

const userStore = useUserStore();
const hasUpdateAccess = computed(() => userStore.role == "admin");
const isCurrDetailImg = computed(() => {
    const currDetailData = currDetail.value;
    if(!currDetailData || !currDetailData.file)
        return false;
    return isFileImg(currDetailData.file);
});
</script>
<template>
    <div>
        <Dialog :header="categoryHeading" v-model:visible="showDialogList" modal maximizable draggable @afterHide="dialog.onListHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="award" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ categorySubHeading }}</h6>
                            <p class="mb-0">
                                {{ dayTitle }}<br>
                                {{ witelName }}
                            </p>
                        </div>
                        <div v-if="!isLoading && hasUpdateAccess" class="col-auto ms-auto mt-4 mt-md-auto">
                            <button type="button" @click="dialog.showFormAdd" class="btn btn-lg btn-primary shadow-sm">
                                <VueFeather type="plus" size="1.2em" class="middle" />
                                <span class="ms-1 middle">Input Baru</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="isLoading">
                    <Skeleton v-for="n in 4" class="block mb-3" />
                </div>
                <div v-else-if="evdList.length > 0">
                    <table class="table table-head-primary table-borderless">
                        <thead>
                            <tr>
                                <th class="text-start">No.</th>
                                <th class="text-start">Deskripsi</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in evdList">
                                <td>{{ index + 1 }}</td>
                                <td>{{ limitWords(item.deskripsi) }}</td>
                                <td class="text-center">
                                    <button v-if="!hasUpdateAccess" @click="dialog.showDetail(item)" class="btn btn-primary">Detail</button>
                                    <ButtonGroupAction v-else @detail="dialog.showDetail(item)" @edit="dialog.showFormEdit(item)" @delete="onDelete(item.id)" class="justify-content-center" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4">
                    <p class="text-center text-muted">
                        <b>Belum ada Evidence.</b>
                        <span v-if="hasUpdateAccess"> Silahkan tekan tombol Tambah untuk menambah item.</span>
                    </p>
                </div>
            </div>
        </Dialog>
        <Dialog header="Input Gepee Evidence" v-model:visible="showDialogAdd" modal maximizable draggable @afterHide="dialog.onFormAddHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="award" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ categorySubHeading }}</h6>
                            <p class="mb-0">
                                {{ dayTitle }}<br>
                                {{ witelName }}
                            </p>
                        </div>
                    </div>
                </div>
                <FormGepeeEvidence v-if="showDialogAdd" :locationId="locationId" @cancel="showDialogAdd = false" @save="dialog.onFormAddSave" />
            </div>
        </Dialog>
        <Dialog header="Update Gepee Evidence" v-model:visible="showDialogEdit" modal maximizable draggable @afterHide="dialog.onFormEditHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="award" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ categorySubHeading }}</h6>
                            <p class="mb-0">
                                {{ dayTitle }}<br>
                                {{ witelName }}
                            </p>
                        </div>
                    </div>
                </div>
                <FormGepeeEvidence v-if="showDialogEdit" :locationId="locationId" :initData="currUpdate" @cancel="showDialogEdit = false" @save="dialog.onFormEditSave" />
            </div>
        </Dialog>
        <Dialog header="Detail Gepee Evidence" v-model:visible="showDialogDetail" modal maximizable draggable @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="award" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ categorySubHeading }}</h6>
                            <p class="mb-0">
                                {{ dayTitle }}<br>
                                {{ witelName }}
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <h6>Deskripsi</h6>
                    <p class="text-muted px-4">{{ currDetail.deskripsi }}</p>
                    <h6>File Evidence</h6>
                    <div v-if="isCurrDetailImg" class="d-flex flex-column align-items-center px-4">
                        <div class="position-relative mb-2">
                            <img :src="currDetail.file_url" alt="gambar evidence" class="img-fluid" role="presentation" />
                            <Image :src="currDetail.file_url" :alt="currDetail.file" preview class="img-stretched-link" />
                        </div>
                        <span class="text-muted">{{ currDetail.file }}</span>
                    </div>
                    <div v-else class="px-4 py-2">
                        <div class="border position-relative">
                            <div class="m-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <DocumentIcon class="tw-w-8 tw-h-8 text-muted" />
                                    </div>
                                    <div class="col position-static">
                                        <a :href="currDetail.file_url" target="_blank" class="stretched-link">{{ currDetail.file }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-5">
                    <button type="button" @click="showDialogDetail = false" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </Dialog>
    </div>
</template>