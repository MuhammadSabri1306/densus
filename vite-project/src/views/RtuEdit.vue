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
import InputGroupLocation from "@components/InputGroupLocation.vue";

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
    kvaGenset: { required },
    portPue: {}
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
    data.portPue = currRtu.port_pue;
    
    isFetching.value = false;
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divreCode = loc.divre_kode;
    data.divreName = loc.divre_name;
    data.witelCode = loc.witel_kode;
    data.witelName = loc.witel_name;
};

const viewStore = useViewStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid || !inputLocation.value.validate())
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
        kva_genset: data.kvaGenset,
        port_pue: data.portPue
    };
    
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
                        <h3>
                            <VueFeather type="hard-drive" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Detail Data RTU</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Master RTU', 'Detail']" class="ms-4" />
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
                                
                                <InputGroupLocation ref="inputLocation" :divreValue="data.divreCode" :witelValue="data.witelCode" @change="onLocationChange" />

                                <div class="row mb-5">
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="portKwh" class="required">Analog Port KW</label>
                                            <input v-model="v$.portKwh.$model" :class="{ 'is-invalid': hasSubmitted && v$.portKwh.$invalid }" class="form-control" id="portKwh" name="portKwh" type="text" placeholder="Cth. A-16">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="portGenset" class="required">Digital Port Status Genset</label>
                                            <input v-model="v$.portGenset.$model" :class="{ 'is-invalid': hasSubmitted && v$.portGenset.$invalid }" class="form-control" id="portGenset" name="portGenset" type="text" placeholder="Cth. D-02">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="kvaGenset" class="required">Kapasitas Genset Terpasang (KVA)</label>
                                            <input v-model="v$.kvaGenset.$model" :class="{ 'is-invalid': hasSubmitted && v$.kvaGenset.$invalid }" class="form-control" id="kvaGenset" name="kvaGenset" type="text" placeholder="Cth. 500">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="portPue">Analog Port PUE</label>
                                            <input v-model="v$.portPue.$model" class="form-control" id="portPue" name="portPue" type="text" placeholder="Cth. A-16">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end pb-3 px-4">
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