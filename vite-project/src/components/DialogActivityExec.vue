<script setup>
import { ref, computed, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useActivityStore } from "@stores/activity";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import ButtonGroupAction from "@components/ButtonGroupAction.vue";
import Skeleton from "primevue/skeleton";
import FormActivityExecAdd from "@components/FormActivityExecAdd.vue";
import FormActivityExecEdit from "@components/FormActivityExecEdit.vue";
import SectionActivityExecDetail from "@components/SectionActivityExecDetail.vue";

const emit = defineEmits(["update", "loaded"]);

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const activityStore = useActivityStore();
const currSchedule = ref(null);

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

const currDateString = () => {
    return new Intl.DateTimeFormat('id', { dateStyle: 'long'}).format() || null;
};

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

const currUpdate = ref(null);
const currDetail = ref(null);

const resetReactiveData = () => {
    currUpdate.value = null;
    currDetail.value = null;
};

const router = useRouter();

const showDialogList = ref(true);
const showDialogDetail = ref(false);
const showDialogAdd = ref(false);
const showDialogEdit = ref(false);

const dialog = {
    onListHide: () => {
        const isOpenOtherDialog = showDialogDetail.value || showDialogAdd.value || showDialogEdit.value;
        if(!isOpenOtherDialog)
            router.push("/gepee/exec");
    },
    onDetailHide: () => {
        resetReactiveData();
        showDialogList.value = true;
    },
    onFormAddHide: () => {
        resetReactiveData();
        showDialogList.value = true;
    },
    onFormEditHide: () => {
        resetReactiveData();
        showDialogList.value = true;
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
        viewStore.showToast("Input Activity", "Berhasil menyimpan Checklist Activity baru.", true);
        fetch();
        showDialogAdd.value = false;
        emit("update");
    },
    onFormEditSave: () => {
        viewStore.showToast("Update Activity", "Berhasil menyimpan Checklist Activity.", true);
        fetch();
        showDialogEdit.value = false;
        emit("update");
    }
};

const onDelete = execId => {
    if(!confirm("Anda akan menghapus Cheklist Activity. Lanjutkan?"))
        return false;
    
    activityStore.deleteExecution(execId, response => {
        if(!response.success)
            return;
        
        viewStore.showToast("Hapus Activity", "Berhasil menghapus Checklist Activity.", true);
        fetch();
        resetReactiveData();
        emit("update");
    });
};

const isEnabled = computed(() => currSchedule.value && currSchedule.value.is_enabled);
</script>
<template>
    <div>
        <Dialog header="Checklist Activity" v-model:visible="showDialogList" modal maximizable draggable @afterHide="dialog.onListHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ title }}</h6>
                            <p class="mb-0">Check Activity {{ currDateString() }}</p>
                            <p class="mb-0">{{ currLocation.sto_name }}</p>
                        </div>
                        <div v-if="!isLoading && isEnabled" class="col-auto ms-auto mt-4 mt-md-auto">
                            <button type="button" @click="dialog.showFormAdd" class="btn btn-lg btn-primary shadow-sm">
                                <VueFeather type="plus" size="1.2em" class="middle" />
                                <span class="ms-1 middle">Tambah</span>
                            </button>
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
                                <td>
                                    <button v-if="item.status == 'approved' || !isEnabled" type="button" @click="dialog.showDetail(item)" class="btn btn-primary">Detail</button>
                                    <ButtonGroupAction v-else @detail="dialog.showDetail(item)" @edit="dialog.showFormEdit(item)" @delete="onDelete(item.id)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-4">
                    <p class="text-center text-muted"><b>Belum ada Checklist.</b> Silahkan tekan tombol Tambah untuk menambahkan Checklist.</p>
                </div>
            </div>
        </Dialog>
        <Dialog header="Detail Activity" v-model:visible="showDialogDetail" modal maximizable draggable @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
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
        <Dialog header="Input Activity" v-model:visible="showDialogAdd" modal maximizable draggable @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ title }}</h6>
                            <p class="mb-0">Check Activity {{ currDateString() }}</p>
                            <p class="mb-0">{{ currLocation.sto_name }}</p>
                        </div>
                    </div>
                </div>
                <FormActivityExecAdd @cancel="showDialogAdd = false" @save="dialog.onFormAddSave" />
            </div>
        </Dialog>
        <Dialog header="Update Activity" v-model:visible="showDialogEdit" modal maximizable draggable @afterHide="dialog.onDetailHide">
            <div class="pb-4 pt-4 pt-md-0">
                <div class="card card-body bg-light text-dark p-t-25 p-b-25 p-l-30 p-r-30">
                    <div class="row">
                        <div class="col-4 col-md-2 mt-1">
                            <VueFeather type="check-circle" class="w-100 font-success" />
                        </div>
                        <div class="col">
                            <h6 class="m-b-5 font-success f-w-700">{{ title }}</h6>
                            <p class="mb-0">Check Activity {{ currDateString() }}</p>
                            <p class="mb-0">{{ currLocation.sto_name }}</p>
                        </div>
                    </div>
                </div>
                <FormActivityExecEdit v-if="currUpdate" :initData="currUpdate" @cancel="showDialogEdit = false" @save="dialog.onFormEditSave" />
            </div>
        </Dialog>
    </div>
</template>