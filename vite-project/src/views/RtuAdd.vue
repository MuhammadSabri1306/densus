<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useRtuStore } from "@/stores/rtu";
import { useNewosaseStore } from "@/stores/newosase";
import { useViewStore } from "@/stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form2";
import { mustBeRtuCode } from "@/helpers/form-validator";
import { toNewosasePortValue } from "@/helpers/number-format";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import InputGroupLocationV2 from "@/components/InputGroupLocationV2.vue";
import ListboxFilterV2 from "@/components/ListboxFilterV2.vue";
import InputSwitch from "primevue/inputswitch";

const router = useRouter();
const rtuStore = useRtuStore();
const newosaseStore = useNewosaseStore();
const viewStore = useViewStore();

const { data, v$, hasSubmitted, isInvalid, getInvalidClass, useErrorTooltip } = useDataForm({
    rtuCode: { required, mustBeRtuCode },
    rtuName: { required },
    location: { required },
    stoCode: { required },
    divreCode: { required },
    divreName: { required },
    witelCode: { required },
    witelName: { required },
    portKwh: { required },
    portGenset: { required },
    portPueV2: {},
    kvaGenset: { required },
    useGepee: { value: false, required },
    idLocGepee: {}
});

const isGepeeStoInvalid = () => hasSubmitted.value && data.useGepee && !data.idLocGepee;

const rtuList = ref([]);
const isRtuListLoading = ref(false);

const portList = ref([]);
const isPortListLoading = ref(false);

const gepeeStoList = ref([]);
const isGepeeStoLoading = ref(false);

const sortNewosaseRtu = rtus => {
    return rtus.sort((a, b) => {
        const nameA = a.rtu_name.toUpperCase();
        const nameB = b.rtu_name.toUpperCase();
        return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
    });
};

const resetRtuData = () => {
    data.rtuCode = null;
    data.rtuName = null;
    data.location = null;
    data.portKwh = null;
    data.portGenset = null;
    data.portPueV2 = null;
    data.stoCode = null;
};

const fetchRtus = (regionalId, witelId) => {
    isRtuListLoading.value = true;
    newosaseStore.getMapViewRtu({ regionalId, witelId }, rtus => {
        rtuList.value = sortNewosaseRtu(rtus);
        isRtuListLoading.value = false;
        if(data.useGepee) {
            isGepeeStoLoading.value = true;
            viewStore.getSto({ divre: data.divreCode, witel: data.witelCode }, "gepee")
                .then(sList => {
                    gepeeStoList.value = sList;
                    if(data.idLocGepee && !sList.find(item => item.id == data.idLocGepee))
                        data.idLocGepee = null;
                })
                .catch(err => console.error(err))
                .finally(() => isGepeeStoLoading.value = false);
        }
    });
};

const fetchPorts = (rtuSname) => {
    isPortListLoading.value = true;
    newosaseStore.getRtuPorts(rtuSname, list => {
        portList.value = list.map(port => {
            const portValue = toNewosasePortValue(port.value, port.units, port.identifier);
            const port_label = `${ port.no_port } | ${ port.port_name } | ${ port.identifier } | ${ portValue }`;
            return { ...port, port_label };
        })
        isPortListLoading.value = false;
    });
};

const onRtuChange = rtu => {
    data.rtuName = rtu?.rtu_name || null;
    data.location = rtu?.locs_name || null;

    let stoCode = null;
    if(rtu?.rtu_sname) {
        const rtuCodePieces = rtu.rtu_sname.split("-");
        if(rtuCodePieces.length > 2)
            stoCode = rtuCodePieces[rtuCodePieces.length - 1];
    }
    data.stoCode = stoCode;
    fetchPorts(rtu?.rtu_sname);
};

const onLocationInit = ({ divre, witel }) => {
    const regionalId = divre?.regional_id || null;
    const witelId = witel?.witel_id || null;
    if(regionalId && witelId)
        fetchRtus(regionalId, witelId);

    data.divreCode = divre?.divre_kode || null;
    data.divreName = divre?.regional_name || null;
    data.witelCode = witel?.witel_kode || null;
    data.witelName = witel?.witel_name || null;
};

const onLocationChange = ({ divre, witel }) => {
    rtuList.value = [];
    gepeeStoList.value = [];
    const regionalId = divre?.regional_id || null;
    const witelId = witel?.witel_id || null;

    if(regionalId && witelId) {
        fetchRtus(regionalId, witelId);
    } else {
        resetRtuData();
        data.idLocGepee = null;
    }

    data.divreCode = divre?.divre_kode || null;
    data.divreName = divre?.regional_name || null;
    data.witelCode = witel?.witel_kode || null;
    data.witelName = witel?.witel_name || null;
};

const onInputUseGepeeChange = () => {
    gepeeStoList.value = [];
    data.idLocGepee = null;
    if(data.useGepee && data.divreCode && data.witelCode) {
        isGepeeStoLoading.value = true;
        viewStore.getSto({ divre: data.divreCode, witel: data.witelCode }, "gepee")
            .then(sList => gepeeStoList.value = sList)
            .catch(err => console.error(err))
            .finally(() => isGepeeStoLoading.value = false);
    }
};

const isSaveLoading = ref(false);
const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    const isGepeeStoValid = !isGepeeStoInvalid();
    if(!isValid || !isGepeeStoValid)
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
        port_pue_v2: data.portPueV2,
        kva_genset: data.kvaGenset,
        use_gepee: data.useGepee
    };
    if(data.useGepee)
        body.id_lokasi_gepee = data.idLocGepee;

    isSaveLoading.value = true;
    rtuStore.create(body, response => {
        isSaveLoading.value = false;
        hasSubmitted.value = true;
        if(!response.success)
            return;
        router.push("/rtu");
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
                                <form @submit.prevent="onSubmit" autocomplete="off">
                                    <InputGroupLocationV2 v-model:divreValue="v$.divreCode.$model" v-model:witelValue="v$.witelCode.$model"
                                        applyUserLevel locationType="newosase" divreLabelKey="regional_name" isDivreRequired isWitelRequired
                                        :isDivreInvalid="isInvalid('divreCode')" :isWitelInvalid="isInvalid('witelCode')"
                                        @init="onLocationInit" @change="onLocationChange" />
                                    <div class="row mb-4 align-items-end">
                                        <div class="col-lg-6 col-xl-8">
                                            <div class="form-group">
                                                <label for="rtuCode" class="required">Nama RTU</label>
                                                <ListboxFilterV2 v-model:list="rtuList" v-model:value="v$.rtuCode.$model" @change="onRtuChange"
                                                    :isLoading="isRtuListLoading" :isInvalid="isInvalid('rtuName')" valueKey="rtu_sname"
                                                    labelKey="rtu_name" inputId="rtuCode" inputPlaceholder="Pilih RTU" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-4">
                                            <div class="form-group">
                                                <label for="rtuCode" class="required">Kode RTU</label>
                                                <input v-model="v$.rtuCode.$model" :class="getInvalidClass('rtuCode', 'is-invalid')"
                                                    class="form-control" id="rtuCode" name="rtuCode" type="text" disabled
                                                    placeholder="Cth. RTU00-D7-BAL" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-8">
                                            <div class="form-group">
                                                <label for="location" class="required">Lokasi</label>
                                                <input v-model="v$.location.$model" :class="getInvalidClass('location', 'is-invalid')"
                                                    class="form-control" id="location" name="location" type="text" placeholder="Cth. STO BALAI KOTA">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-4">
                                            <div class="form-group">
                                                <label for="stoCode" class="required">Kode STO</label>
                                                <input v-model="v$.stoCode.$model" :class="getInvalidClass('stoCode', 'is-invalid')"
                                                    class="form-control" id="stoCode" name="stoCode" type="text" placeholder="Cth. BAL">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="portKwh" class="required">Analog Port KW</label>
                                                <ListboxFilterV2 v-model:list="portList" v-model:value="v$.portKwh.$model"
                                                    :isLoading="isPortListLoading" :isInvalid="isInvalid('portKwh')" valueKey="no_port"
                                                    labelKey="port_label" inputId="portKwh" inputPlaceholder="Pilih PORT" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="portGenset" class="required">Digital Port Status Genset</label>
                                                <ListboxFilterV2 v-model:list="portList" v-model:value="v$.portGenset.$model"
                                                    :isLoading="isPortListLoading" :isInvalid="isInvalid('portGenset')" valueKey="no_port"
                                                    labelKey="port_label" inputId="portGenset" inputPlaceholder="Pilih PORT" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group">
                                                <label for="portPueV2">Analog Port PUE</label>
                                                <ListboxFilterV2 v-model:list="portList" v-model:value="v$.portPueV2.$model"
                                                    :isLoading="isPortListLoading" :isInvalid="isInvalid('portPueV2')" valueKey="no_port"
                                                    labelKey="port_label" inputId="portPueV2" inputPlaceholder="Pilih PORT" />
                                            </div>
                                        </div>
                                        <div class="col-lg-10 col-xl-8">
                                            <div class="form-group">
                                                <label for="kvaGenset" class="required">Kapasitas Genset Terpasang (KVA)</label>
                                                <input v-model="v$.kvaGenset.$model" :class="getInvalidClass('kvaGenset', 'is-invalid')"
                                                    class="form-control" id="kvaGenset" name="kvaGenset" type="text" placeholder="Cth. 500">
                                            </div>
                                        </div>
                                    </div>
                                    <p>Apakah anda ingin menghubungkan lokasi RTU dengan Lokasi GEPEE?</p>
                                    <div class="px-4">
                                        <div class="row align-items-center">
                                            <div class="col-auto mb-2">
                                                <InputSwitch v-model="data.useGepee" :disabled="isSaveLoading" @change="onInputUseGepeeChange"
                                                    inputId="switchUseGepee" />
                                            </div>
                                            <div class="col-auto mb-2">
                                                <label for="switchUseGepee">Hubungkan</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-10 col-xl-8">
                                            <div class="form-group ps-4 mb-5">
                                                <label id="gepeeLoc" :class="{ 'required': data.useGepee }">Pilih Lokasi</label>
                                                <ListboxFilterV2 v-model:list="gepeeStoList" v-model:value="v$.idLocGepee.$model"
                                                    :isLoading="isGepeeStoLoading" :isInvalid="isGepeeStoInvalid()" valueKey="id"
                                                    labelKey="sto_name" inputId="gepeeLoc" inputPlaceholder="Pilih Lokasi GEPEE" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end px-4">
                                        <button type="submit" :disabled="isSaveLoading" :class="{ 'btn-loading': isSaveLoading }"
                                            class="btn btn-primary btn-lg">Simpan RTU</button>
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