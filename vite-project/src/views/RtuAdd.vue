<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useRtuStore } from "@stores/rtu";
import { useViewStore } from "@stores/view";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import InputGroupLocation from "@components/InputGroupLocation.vue";
import InputSwitch from "primevue/inputswitch";
import ListboxFilter from "@components/ListboxFilter.vue";

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
    portPue: {},
    useGepee: { value: false, required },
    idLocGepee: {}
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divreCode = loc.divre_kode;
    data.divreName = loc.divre_name;
    data.witelCode = loc.witel_kode;
    data.witelName = loc.witel_name;
};

const viewStore = useViewStore();
const router = useRouter();

const listboxGepeeSto = ref(null);

const watcherSrc = () => {
    const useGepee = data.useGepee;
    const divre = data.divreCode;
    const witel = data.witelCode;
    return { useGepee, divre, witel };
};

const watcherCall = ({ useGepee, divre, witel }) => {
    if(!useGepee) {
        if(listboxGepeeSto.value)
            listboxGepeeSto.value.setDisabled(true);
        data.idLocGepee = null;
        return;
    }

    if(listboxGepeeSto.value) {
        listboxGepeeSto.value.setDisabled(false);
        listboxGepeeSto.value.fetch(
            () => viewStore.getSto({ divre, witel }, "gepee"),
            list => {
                const index = list.findIndex(item => item.id == data.idLocGepee);
                if(index >= 0)
                    return;
                listboxGepeeSto.value.setValue(null);
                data.idLocGepee = null;
            }
        );
    }
};

watch(watcherSrc, watcherCall);
const onGepeeLocChange = idLocGepee => data.idLocGepee = idLocGepee;

const validateStoGepee = () => {
    if(!data.useGepee)
        return true;

    listboxGepeeSto.value.validate();
    return data.idLocGepee ? true : false;
};

const isLoading = ref(false);
const hasSubmitted = ref(false);
const rtuStore = useRtuStore();

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    const isWitelValid = inputLocation.value.validate();
    const isGepeeLocValid = validateStoGepee();

    if(!isValid || !isWitelValid || !isGepeeLocValid)
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
        port_pue: data.portPue,
        use_gepee: data.useGepee
    };
    if(data.useGepee)
        body.id_lokasi_gepee = data.idLocGepee;

    isLoading.value = true;
    rtuStore.create(body, response => {
        isLoading.value = false;
        hasSubmitted.value = true;
        if(!response.success)
            return;

        viewStore.showToast("Data RTU", "Berhasil menyimpan data.", true);
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
                            <span class="middle ms-3">Registrasi RTU Baru</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Master RTU', 'Registrasi']" class="ms-4" />
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
                            <div class="px-md-4 py-3">
                                <form @submit.prevent="onSubmit">
                                    <div class="form-group">
                                        <label for="rtuCode" class="required">Kode RTU</label>
                                        <input v-model="v$.rtuCode.$model" :class="{ 'is-invalid': hasSubmitted && v$.rtuCode.$invalid }" class="form-control" id="rtuCode" name="rtuCode" type="text" placeholder="Cth. RTU-BALA">
                                    </div>
                                    <div class="form-group">
                                        <label for="rtuName" class="required">Nama RTU</label>
                                        <input v-model="v$.rtuName.$model" :class="{ 'is-invalid': hasSubmitted && v$.rtuName.$invalid }" class="form-control" id="rtuName" name="rtuName" type="text" placeholder="Cth. RTU STO BALAI KOTA">
                                    </div>
                                    <div class="form-group">
                                        <label for="location" class="required">Lokasi</label>
                                        <input v-model="v$.location.$model" :class="{ 'is-invalid': hasSubmitted && v$.location.$invalid }" class="form-control" id="location" name="location" type="text" placeholder="Cth. STO BALAI KOTA">
                                    </div>
                                    <div class="form-group">
                                        <label for="stoCode" class="required">Kode STO</label>
                                        <input v-model="v$.stoCode.$model" :class="{ 'is-invalid': hasSubmitted && v$.stoCode.$invalid }" class="form-control" id="stoCode" name="stoCode" type="text" placeholder="Cth. BAL">
                                    </div>
                                    
                                    <InputGroupLocation ref="inputLocation" :divreValue="data.divreCode" :witelValue="data.witelCode" @change="onLocationChange" />
    
                                    <div class="row mb-4">
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
                                    <p>Apakah anda ingin menghubungkan lokasi RTU dengan Lokasi GEPEE?</p>
                                    <div class="px-4">
                                        <div class="row align-items-center">
                                            <div class="col-auto mb-2">
                                                <InputSwitch v-model="data.useGepee" inputId="switchUseGepee" />
                                            </div>
                                            <div class="col-auto mb-2">
                                                <label for="switchUseGepee">Hubungkan</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group ps-4 mb-5">
                                        <label id="inputGepeeLocation" :class="{ 'required': data.useGepee }">Pilih Lokasi</label>
                                        <ListboxFilter ref="listboxGepeeSto" inputId="inputGepeeLocation" inputPlaceholder="Pilih Lokasi GEPEE"
                                            :isRequired="data.useGepee" valueKey="id" labelKey="sto_name" @change="onGepeeLocChange" />
                                    </div>
                                    <div class="d-flex justify-content-end px-4">
                                        <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-primary btn-lg">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>