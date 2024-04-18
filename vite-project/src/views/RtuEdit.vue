<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useRtuStore } from "@/stores/rtu";
import { useNewosaseStore } from "@/stores/newosase";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form2";
import { mustBeRtuCode } from "@/helpers/form-validator";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import InputGroupLocationV2 from "@/components/InputGroupLocationV2.vue";
import ListboxFilterV2 from "@/components/ListboxFilterV2.vue";
import InputSwitch from "primevue/inputswitch";
import Skeleton from "primevue/skeleton";

const route = useRoute();
const router = useRouter();
const rtuStore = useRtuStore();
const newosaseStore = useNewosaseStore();
const userStore = useUserStore();
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
    kvaGenset: { required },
    portPue: {},
    portPueV2: {},
    useGepee: { value: false, required },
    idLocGepee: {}
});

const isGepeeStoInvalid = () => hasSubmitted.value && data.useGepee && !data.idLocGepee;

const rtuId = computed(() => route.params.rtuId);
const rtuList = ref([]);
const isRoleAdmin = computed(() => userStore.role == "admin");
const gepeeStoList = ref([]);

const isRtuListLoading = ref(false);
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
    data.stoCode = null;
};

const currRtu = ref(null);
const setRtuDataByCurrRtu = () => {
    const rtu = currRtu.value;
    data.rtuCode = rtu?.rtu_kode || null;
    data.rtuName = rtu?.rtu_name || null;
    data.location = rtu?.lokasi || null;
    data.stoCode = rtu?.sto_kode || null;
};
const setDataByCurrRtu = () => {
    const rtu = currRtu.value;
    setRtuDataByCurrRtu();
    data.divreCode = rtu?.divre_kode || null;
    data.divreName = rtu?.divre_name || null;
    data.witelCode = rtu?.witel_kode || null;
    data.witelName = rtu?.witel_name || null;
    data.portKwh = rtu?.port_kwh || null;
    data.portGenset = rtu?.port_genset || null;
    data.kvaGenset = rtu?.kva_genset || null;
    data.portPue = rtu?.port_pue || null;
    data.portPueV2 = rtu?.port_pue_v2 || null;

    if(rtu?.id_lokasi_gepee) {
        data.idLocGepee = rtu?.id_lokasi_gepee || null;
        data.useGepee = true;
    }
};

const onRtuChange = rtu => {
    data.rtuCode = rtu?.rtu_sname || null;
    data.rtuName = rtu?.rtu_name || null;
    data.location = rtu?.locs_name || null;

    let stoCode = null;
    if(rtu?.rtu_sname) {
        const rtuCodePieces = rtu.rtu_sname.split("-");
        if(rtuCodePieces.length > 2)
            stoCode = rtuCodePieces[rtuCodePieces.length - 1];
    }
    data.stoCode = stoCode;
};

const onLocationInit = ({ divre, witel }) => {
    const regionalId = divre?.regional_id || null;
    const witelId = witel?.witel_id || null;

    if(regionalId && witelId) {
        isRtuListLoading.value = true;
        newosaseStore.getMapViewRtu({ regionalId, witelId }, rtus => {
            if(!rtus.find(item => item.rtu_sname == data.rtuCode)) {
                resetRtuData();
            }
            rtuList.value = sortNewosaseRtu(rtus);
            isRtuListLoading.value = false;
            if(data.useGepee) {
                isGepeeStoLoading.value = true;
                viewStore.getSto({ divre: data.divreCode, witel: data.witelCode }, "gepee")
                    .then(sList => gepeeStoList.value = sList)
                    .catch(err => console.error(err))
                    .finally(() => isGepeeStoLoading.value = false);
            }
        });
    } else {
        resetRtuData();
    }

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
        isRtuListLoading.value = true;
        newosaseStore.getMapViewRtu({ regionalId, witelId }, rtus => {
            if(!rtus.find(item => item.rtu_sname == data.rtuCode))
                resetRtuData();
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
    } else {
        resetRtuData();
        data.idLocGepee = null;
    }

    data.divreCode = divre?.divre_kode || null;
    data.divreName = divre?.regional_name || null;
    data.witelCode = witel?.witel_kode || null;
    data.witelName = witel?.witel_name || null;
};

const isGepeeStoLoading = ref(false);
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

const showUpdateForm = ref(false);
const isRtuLoading = ref(false);
const fetch = () => {
    isRtuLoading.value = true;
    rtuStore.getDetail(rtuId.value, rtu => {
        currRtu.value = rtu;
        if(!rtu) {
            isRtuLoading.value = false;
            router.push("/e404");
            return;
        }
        setDataByCurrRtu();
        isRtuLoading.value = false;
    });
};

const onReset = () => {
    showUpdateForm.value = false;
    setDataByCurrRtu();
};

const isUpdateLoading = ref(false);
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
        kva_genset: data.kvaGenset,
        port_pue: data.portPue,
        port_pue_v2: data.portPueV2,
        use_gepee: data.useGepee
    };
    if(data.useGepee)
        body.id_lokasi_gepee = data.idLocGepee;

    isUpdateLoading.value = true;
    rtuStore.update(rtuId.value, body, response => {
        isUpdateLoading.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Data RTU", "Berhasil menyimpan data.", true);
        showUpdateForm.value = false;
        fetch();
    });
};

const onDelete = () => {
    const deleteRtu = confirm("Anda akan menghapus data RTU. Lanjutkan?");
    if(!deleteRtu)
        return;
    isUpdateLoading.value = true;
    rtuStore.delete(rtuId.value, response => {
        isUpdateLoading.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Data RTU", "Data RTU berhasil dihapus.", true);
        router.push("/rtu");
    });
};

fetch();
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
                        <div v-if="isRoleAdmin" class="card-header d-flex pb-0">
                            <h5 v-if="!showUpdateForm" class="card-title">Detail RTU</h5>
                            <h5 v-else class="card-title">Form Update RTU</h5>
                            <div v-if="!showUpdateForm" class="position-absolute end-0 top-0 p-2">
                                <button type="button" @click="onDelete" :disabled="isUpdateLoading"
                                    class="btn btn-danger btn-pill p-0 tw-w-10 tw-h-10 tw-transition-opacity tw-opacity-50 hover:tw-opacity-70">
                                    <VueFeather type="trash-2" size="1.2rem" class="middle" />
                                </button>
                            </div>
                        </div>
                        <div v-else class="card-header d-flex pb-0">
                            <h5 class="card-title">Detail RTU</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="isRtuLoading">
                                <Skeleton width="60%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="30%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="70%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="90%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                            </div>
                            <div v-else class="px-md-4 py-3">
                                <form @submit.prevent="onSubmit" autocomplete="off" :class="{ 'update-form': showUpdateForm }">
                                    <InputGroupLocationV2 v-model:divreValue="v$.divreCode.$model" v-model:witelValue="v$.witelCode.$model"
                                        applyUserLevel locationType="newosase" divreLabelKey="regional_name" isDivreRequired isWitelRequired
                                        :isDivreDisabled="!showUpdateForm" :isWitelDisabled="!showUpdateForm" :isDivreInvalid="isInvalid('divreCode')"
                                        :isWitelInvalid="isInvalid('witelCode')" @change="onLocationChange" @init="onLocationInit" />
                                    <div class="form-group">
                                        <label for="rtuCode" class="required">Nama RTU</label>
                                        <ListboxFilterV2 v-model:list="rtuList" v-model:value="v$.rtuCode.$model" @change="onRtuChange"
                                            :isLoading="isRtuListLoading" :isDisabled="!showUpdateForm" :isInvalid="isInvalid('rtuName')"
                                            valueKey="rtu_sname" labelKey="rtu_name" inputId="rtuCode" inputPlaceholder="Pilih RTU" />
                                    </div>
                                    <div class="form-group">
                                        <label for="rtuCode" class="required">Kode RTU</label>
                                        <input v-model="v$.rtuCode.$model" :class="getInvalidClass('rtuCode', 'is-invalid')"
                                            class="form-control" id="rtuCode" name="rtuCode" type="text" disabled
                                            placeholder="Cth. RTU00-D7-BAL" />
                                    </div>
                                    <div class="form-group">
                                        <label for="location" class="required">Lokasi</label>
                                        <input v-model="v$.location.$model" :class="getInvalidClass('location', 'is-invalid')" :disabled="!showUpdateForm"
                                            class="form-control" id="location" name="location" type="text" placeholder="Cth. STO BALAI KOTA">
                                    </div>
                                    <div class="form-group">
                                        <label for="stoCode" class="required">Kode STO</label>
                                        <input v-model="v$.stoCode.$model" :class="getInvalidClass('stoCode', 'is-invalid')" :disabled="!showUpdateForm"
                                            class="form-control" id="stoCode" name="stoCode" type="text" placeholder="Cth. BAL">
                                    </div>
                                    <div class="row mb-4 align-items-end">
                                        <div class="col-md-6 col-lg-auto">
                                            <div class="form-group">
                                                <label for="portKwh" class="required">Analog Port KW</label>
                                                <input v-model="v$.portKwh.$model" :class="getInvalidClass('portKwh', 'is-invalid')" :disabled="!showUpdateForm"
                                                    class="form-control" id="portKwh" name="portKwh" type="text" placeholder="Cth. A-16">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-auto">
                                            <div class="form-group">
                                                <label for="portGenset" class="required">Digital Port Status Genset</label>
                                                <input v-model="v$.portGenset.$model" :class="getInvalidClass('portGenset', 'is-invalid')" :disabled="!showUpdateForm"
                                                    class="form-control" id="portGenset" name="portGenset" type="text" placeholder="Cth. D-02">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg">
                                            <div class="form-group">
                                                <label for="kvaGenset" class="required">Kapasitas Genset Terpasang (KVA)</label>
                                                <input v-model="v$.kvaGenset.$model" :class="getInvalidClass('kvaGenset', 'is-invalid')" :disabled="!showUpdateForm"
                                                    class="form-control" id="kvaGenset" name="kvaGenset" type="text" placeholder="Cth. 500">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-auto">
                                            <div class="form-group">
                                                <label for="portPue">Analog Port PUE</label>
                                                <input v-model="v$.portPue.$model" class="form-control" id="portPue" name="portPue" :disabled="!showUpdateForm"
                                                    type="text" placeholder="Cth. A-92">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-auto">
                                            <div class="form-group">
                                                <label for="portPue">Analog Port PUE (NewOsase Baru)</label>
                                                <input v-model="v$.portPueV2.$model" class="form-control" id="portPueV2" name="portPueV2" :disabled="!showUpdateForm"
                                                    type="text" placeholder="Cth. A-92">
                                            </div>
                                        </div>
                                    </div>
                                    <p>Apakah anda ingin menghubungkan lokasi RTU dengan Lokasi GEPEE?</p>
                                    <div class="px-4">
                                        <div class="row align-items-center">
                                            <div class="col-auto mb-2">
                                                <InputSwitch v-model="data.useGepee" :disabled="isUpdateLoading || !showUpdateForm"
                                                    @change="onInputUseGepeeChange" inputId="switchUseGepee" />
                                            </div>
                                            <div class="col-auto mb-2">
                                                <label for="switchUseGepee">Hubungkan</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group ps-4 mb-5">
                                        <label id="inputGepeeLocation" :class="{ 'required': data.useGepee }">Pilih Lokasi</label>
                                        <ListboxFilterV2 v-model:list="gepeeStoList" v-model:value="v$.idLocGepee.$model" :isLoading="isGepeeStoLoading"
                                            :isDisabled="!showUpdateForm" :isInvalid="isGepeeStoInvalid()" valueKey="id" labelKey="sto_name"
                                            inputId="inputGepeeLocation" inputPlaceholder="Pilih Lokasi GEPEE" />
                                    </div>
                                    <div v-if="isRoleAdmin">
                                        <div v-if="showUpdateForm" class="d-flex justify-content-end align-items-center px-4 tw-gap-x-8 tw-gap-y-6">
                                            <button type="reset" :disabled="isUpdateLoading" @click="onReset"
                                                class="btn btn-sm btn-danger">Batalkan</button>
                                            <button type="submit" :disabled="isUpdateLoading" :class="{ 'btn-loading': isUpdateLoading }"
                                                class="btn btn-primary btn-lg">Simpan Perubahan</button>
                                        </div>
                                        <div v-else class="d-flex justify-content-end px-4">
                                            <button type="button" @click="showUpdateForm = true"
                                                class="btn btn-primary">Update RTU</button>
                                        </div>
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
<style scoped>

form:not(.update-form) .form-control,
form:not(.update-form) :deep(.form-control) {
    @apply tw-border-transparent tw-bg-transparent;
}

</style>