<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useActivityStore } from "@/stores/activity";
import { useViewStore } from "@/stores/view";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const activityStore = useActivityStore();

const currSchedule = computed(() => activityStore.schedule.find(item => item.id == scheduleId.value)|| null);

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

const executionList = computed(() => activityStore.execution);

activityStore.fetchCategory();

const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    activityStore.fetchExecution(scheduleId.value, true, () => isLoading.value = false);
};

const viewStore = useViewStore();
const onApproved = execId => {
    if(!confirm("Anda akan mengganti status menjadi Approved. Lanjutkan?"))
        return false;
    
    activityStore.approveExecution(execId, response => {
        if(!response.success)
            return;
        viewStore.showToast("Approve Activity", "Berhasil melakukan Approve pada Activity", true);
        fetch();
    });
};

const currReject = ref(null);
const rejectDesc = ref(null);
const isRejectSubmitted = ref(false);

const resetRejection = () => {
    currReject.value = null;
    rejectDesc.value = null;
    isRejectSubmitted.value = false;
};

const onReject = () => {
    const execId = currReject.value.id;
    isRejectSubmitted.value = true;
    if(!rejectDesc.value || rejectDesc.value.length < 1)
        return;
    if(!confirm("Anda akan mengganti status menjadi Rejected. Lanjutkan?"))
        return false;

    activityStore.rejectExecution(execId, rejectDesc.value, response => {
        if(!response.success)
            return;
        viewStore.showToast("Reject Activity", "Telah melakukan Reject pada Activity", true);
        fetch();
        resetRejection();
    });
};
</script>
<template>
    <Dialog header="Checklist Activity" modal maximizable draggable class="dialog-basic"
        @show="fetch" @hide="$router.push('/gepee/exec')">
        <div class="pb-4 pt-4 pt-md-0">
            <div class="row align-items-end">
                <div class="col-auto mb-4">
                    <h4>{{ title }}</h4>
                    <p><small>Check Activity {{ currDateString() }}</small></p>
                    <p>{{ currLocation.sto_name }}</p>
                </div>
            </div>
            <div v-if="isLoading">
                <Skeleton v-for="n in 4" class="block mb-3" />
            </div>
            <form v-else-if="currReject" @submit.prevent="onReject">
                <div class="mb-2">
                    <label>Judul Activity</label>
                    <p class="ms-2">{{ currReject.title }}</p>
                </div>
                <div class="mb-5">
                    <label for="textRejectDesc">Alasan Reject<span class="text-danger"> *</span></label>
                    <textarea v-model="rejectDesc" :class="{ 'is-invalid': (isRejectSubmitted && !rejectDesc) || isRejectSubmitted && rejectDesc.length < 1 }" class="form-control" rows="5"></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="submit" class="btn btn-lg btn-danger">Reject</button>
                    <button type="button" @click="resetRejection" class="btn btn-info">Kembali</button>
                </div>
            </form>
            <div v-else-if="executionList.length > 0">
                <table class="table table-head-primary">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Judul Activity</th>
                            <th>Status</th>
                            <th colspan="2" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in executionList">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.title }}</td>
                            <td>
                                <b :class="{ 'text-muted': item.status == 'submitted', 'text-danger': item.status == 'rejected', 'text-success': item.status == 'approved' }" class="text-capitalize">{{ item.status }}</b>
                            </td>
                            <td>
                                <button type="button" @click="onApproved(item.id)" class="btn btn-sm btn-primary px-3 py-1 d-flex justify-content-center">
                                    <VueFeather type="check" size="1rem" class="middle" />
                                    <span class="ms-1 middle">Approved</span>
                                </button>
                            </td>
                            <td>
                                <button type="button" @click="currReject = item" class="btn btn-sm btn-danger px-3 py-1 d-flex justify-content-center">
                                    <VueFeather type="x" size="1rem" class="middle" />
                                    <span class="ms-1 middle">Rejected</span>
                                </button>
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
</template>