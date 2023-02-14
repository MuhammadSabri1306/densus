<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useRtuStore } from "@stores/rtu";
import { useViewStore } from "@stores/view";
import { useDataForm, buildFormData } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import http from "@helpers/http-common";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import ListboxRegWitel from "@components/ListboxRegWitel.vue";
import Skeleton from "primevue/skeleton";

const route = useRoute();
const router = useRouter();
const rtuId = computed(() => route.params.rtuId);

const { data, v$ } = useDataForm({
    rtuCode: { required },
    rtuName: { required },
    location: { required },
    stoCode: { required },
    divreCode: { required },
    divreName: { required },
    witelCode: { required },
    witelName: { required },
    portKwh: { required },
    portGenset: { required },
    kvaGenset: { required }
});

const rtuStore = useRtuStore();
const rtuList = computed(() => rtuStore.list);

const isFetching = ref(true);
rtuStore.fetchList(false, response => {
    if(!response.success) {
        isFetching.value = false;
        return;
    }

    const currRtu = rtuList.value.find(item => item.id == rtuId.value);
    if(!currRtu) {
        router.push("/e404");
        return;
    }
    console.log(currRtu);
    data.rtuCode = currRtu.rtu_kode;
    data.rtuName = currRtu.rtu_name;
    data.location = currRtu.lokasi;
    data.stoCode = currRtu.sto_kode;
    data.divreCode = currRtu.divre_kode;
    data.divreName = currRtu.divre_name;
    data.witelCode = currRtu.witel_kode;
    data.witelName = currRtu.witel_name;
    data.portKwh = currRtu.port_kwh;
    data.portGenset = currRtu.port_genset;
    data.kvaGenset = currRtu.kva_genset;
    isFetching.value = false;
});

const onDivreChange = val => {
    data.divreCode = val.divreCode;
    data.divreName = val.divreName;
};
const onWitelChange = val => {
    data.witelCode = val.witelCode;
    data.witelName = val.witelName;
};

const listboxRegWitel = ref(null);
const viewStore = useViewStore();

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    listboxRegWitel.value.validate();
    const isValid = await v$.value.$validate();

    if(!isValid)
        return;
    const body = {
        rtu_kode: data.rtuCode,
        rtu_name: data.rtuName,
        lokasi: data.location,
        sto_kode: data.stoCode,
        divre_kode: data.divreCode,
        divre_name: data.divreName,
        witel_kode: data.witelCode,
        witel_name: data.witelName,
        port_kwh: data.portKwh,
        port_genset: data.portGenset,
        kva_genset: data.kvaGenset
    };
    // console.log(body);
    // const formData = buildFormData(data, ["rtuCode", "rtuName", "location", "stoCode", "divreCode", "divreName", "witelCode", "witelName", "portKwh", "portGenset", "kvaGenset"]);
    isLoading.value = true;
    rtuStore.update(rtuId.value, body, response => {
        isLoading.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Data RTU", "Berhasil menyimpan data.", true);
        rtuStore.fetchList(true);
    });
};

const onDelete = () => {
    const deleteRtu = confirm("Anda akan menghapus data RTU. Lanjutkan?");
    if(!deleteRtu)
        return;
    isLoading.value = true;
    rtuStore.delete(rtuId.value, response => {
        isLoading.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Data RTU", "Data RTU berhasil dihapus.", true);
        rtuStore.fetchList(true, () => router.push("/rtu"));
    });
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Detail Data RTU</h3>
                        <DashboardBreadcrumb :items="['Master RTU', 'Detail']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-12 col-lg-10">
                    <div class="card">
                        <div class="card-header d-flex pb-0">
                            <h5 class="card-title">Form Registrasi RTU</h5>
                            <div class="position-absolute end-0 top-0 p-2">
                                <button type="button" @click="onDelete" class="btn btn-danger btn-pill p-0 tw-w-10 tw-h-10 tw-transition-opacity tw-opacity-50 hover:tw-opacity-70">
                                    <VueFeather type="trash-2" size="1.2rem" class="middle" />
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div v-if="isFetching">
                                <Skeleton width="60%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="30%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="70%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="90%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                            </div>
                            <form v-else @submit.prevent="onSubmit">
                                <div class="form-group">
                                    <label for="rtuCode">Kode RTU <span class="text-danger">*</span></label>
                                    <input v-model="v$.rtuCode.$model" :class="{ 'is-invalid': hasSubmitted && v$.rtuCode.$invalid }" class="form-control" id="rtuCode" name="rtuCode" type="text" placeholder="Cth. RTU-BALA">
                                </div>
                                <div class="form-group">
                                    <label for="rtuName">Nama RTU <span class="text-danger">*</span></label>
                                    <input v-model="v$.rtuName.$model" :class="{ 'is-invalid': hasSubmitted && v$.rtuName.$invalid }" class="form-control" id="rtuName" name="rtuName" type="text" placeholder="Cth. RTU STO BALAI KOTA">
                                </div>
                                <div class="form-group">
                                    <label for="location">Lokasi <span class="text-danger">*</span></label>
                                    <input v-model="v$.location.$model" :class="{ 'is-invalid': hasSubmitted && v$.location.$invalid }" class="form-control" id="location" name="location" type="text" placeholder="Cth. STO BALAI KOTA">
                                </div>
                                <div class="form-group">
                                    <label for="stoCode">Kode STO <span class="text-danger">*</span></label>
                                    <input v-model="v$.stoCode.$model" :class="{ 'is-invalid': hasSubmitted && v$.stoCode.$invalid }" class="form-control" id="stoCode" name="stoCode" type="text" placeholder="Cth. BAL">
                                </div>
                                
                                <ListboxRegWitel ref="listboxRegWitel" fieldRequired :defaultDivre="data.divreCode" :defaultWitel="data.witelCode" @divreChange="onDivreChange" @witelChange="onWitelChange" class="mb-4" />

                                <div class="row">
                                    <div class="col-7 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="portKwh">Port KWH <span class="text-danger">*</span></label>
                                            <input v-model="v$.portKwh.$model" :class="{ 'is-invalid': hasSubmitted && v$.portKwh.$invalid }" class="form-control" id="portKwh" name="portKwh" type="text" placeholder="Cth. A-16">
                                        </div>
                                    </div>
                                    <div class="col-7 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="portGenset">Port Genset <span class="text-danger">*</span></label>
                                            <input v-model="v$.portGenset.$model" :class="{ 'is-invalid': hasSubmitted && v$.portGenset.$invalid }" class="form-control" id="portGenset" name="portGenset" type="text" placeholder="Cth. D-02">
                                        </div>
                                    </div>
                                    <div class="col-7 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="kvaGenset">KVA Genset <span class="text-danger">*</span></label>
                                            <input v-model="v$.kvaGenset.$model" :class="{ 'is-invalid': hasSubmitted && v$.kvaGenset.$invalid }" class="form-control" id="kvaGenset" name="kvaGenset" type="text" placeholder="Cth. 500">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end py-3 px-4">
                                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>