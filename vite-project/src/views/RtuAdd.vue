<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useDataForm, buildFormData } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import http from "@helpers/http-common";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import ListboxRegWitel from "@components/ListboxRegWitel.vue";

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
const router = useRouter();

const isLoading = ref(false);
const hasSubmitted = ref(false);

const sendReq = (formData) => {
    isLoading.value = true;
    const headers = { "Authorization": "Bearer 123" };

    http.post("/rtu", formData, { headers })
        .then(response => {
            if(!response.data.success) {
                console.warn(response);
                return;
            }
            isLoading.value = false;
            viewStore.showToast("Data RTU", "Berhasil menyimpan data.", true);
            router.push("/rtu");
        })
        .catch(err => {
            isLoading.value = false;
            viewStore.showToast("Koneksi gagal", "Terjadi masalah saat menghubungi server.", false);
            console.error(err);
        });
};

const onSubmit = async () => {
    hasSubmitted.value = true;
    listboxRegWitel.value.validate();
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;

    const formData = buildFormData(data, ["rtuCode", "rtuName", "location", "stoCode", "divreCode", "divreName", "witelCode", "witelName", "portKwh", "portGenset", "kvaGenset"]);
    sendReq(formData);
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Registrasi RTU Baru</h3>
                        <DashboardBreadcrumb :items="['Master RTU', 'Registrasi']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-12 col-lg-10">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="card-title">Form Registrasi RTU</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="onSubmit">
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
                                
                                <ListboxRegWitel ref="listboxRegWitel" fieldRequired @divreChange="onDivreChange" @witelChange="onWitelChange" class="mb-4" />

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
                                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-primary btn-lg">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>