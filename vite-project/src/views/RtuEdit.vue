<script setup>
import { ref, computed, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useRtuStore } from "@/stores/rtu";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form2";
import { mustBeRtuCode } from "@/helpers/form-validator";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import Skeleton from "primevue/skeleton";
import InputGroupLocation from "@/components/InputGroupLocation.vue";
import InputSwitch from "primevue/inputswitch";
import ListboxFilter from "@/components/ListboxFilter.vue";

const route = useRoute();
const router = useRouter();
const rtuId = computed(() => route.params.rtuId);

const userStore = useUserStore();
const isRoleAdmin = computed(() => userStore.role == "admin");

const { data, v$, hasSubmitted, getInvalidClass, useErrorTooltip } = useDataForm({
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

const viewStore = useViewStore();
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
                if(index >= 0) {
                    listboxGepeeSto.value.setValue(data.idLocGepee);
                    return;
                }
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

const rtuStore = useRtuStore();
const currRtu = ref(null);
const setDataByCurrRtu = () => {
    const rtu = currRtu.value ?? {};
    data.rtuCode = rtu.rtu_kode;
    data.rtuName = rtu.rtu_name;
    data.location = rtu.lokasi;
    data.stoCode = rtu.sto_kode;
    data.divreCode = rtu.divre_kode;
    data.divreName = rtu.divre_name;
    data.witelCode = rtu.witel_kode;
    data.witelName = rtu.witel_name;
    data.portKwh = rtu.port_kwh;
    data.portGenset = rtu.port_genset;
    data.kvaGenset = rtu.kva_genset;
    data.portPue = rtu.port_pue;
    data.portPueV2 = rtu.port_pue_v2;

    if(rtu.id_lokasi_gepee) {
        data.idLocGepee = rtu.id_lokasi_gepee;
        data.useGepee = true;
        nextTick(() => {
            watcherCall(watcherSrc());
        });
    }
};

const isLoading = ref(false);
const fetch = () => {
    isLoading.value = true;
    rtuStore.getDetail(rtuId.value, rtu => {
        currRtu.value = rtu ?? {};
        if(!rtu) {
            isLoading.value = false;
            router.push("/e404");
            return;
        }
        setDataByCurrRtu();
        isLoading.value = false;
    });
};

const onReset = () => {
    showForm.value = false;
    setDataByCurrRtu();
};

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divreCode = loc.divre_kode;
    data.divreName = loc.divre_name;
    data.witelCode = loc.witel_kode;
    data.witelName = loc.witel_name;
};

const useRtuCodeTooltip = (field) => {
    const message = "Kode RTU tidak dapat dikosongkan, hanya menerima karakter Alfanumerik (A-Z, 0-1) dan tanda '-'";
    return useErrorTooltip({ field, message, afterSubmit: false });
};

const showForm = ref(false);
const isUpdateLoading = ref(false);

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
        showForm.value = false;
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
        router.push("/rtu")
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
                            <h5 v-if="!showForm" class="card-title">Detail RTU</h5>
                            <h5 v-else class="card-title">Form Update RTU</h5>
                            <div v-if="!showForm" class="position-absolute end-0 top-0 p-2">
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
                            <div v-if="isLoading">
                                <Skeleton width="60%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="30%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="70%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="90%" height="2rem" borderRadius="1rem" class="mb-3" />
                                <Skeleton width="80%" height="2rem" borderRadius="1rem" class="mb-3" />
                            </div>
                            <div v-else class="px-md-4 py-3">
                                <form @submit.prevent="onSubmit">
                                    <div :class="{ 'is-editable': showForm }" class="form-section">
                                        <div class="form-group">
                                            <label for="rtuCode">Kode RTU <span class="text-danger">*</span></label>
                                            <input v-model="v$.rtuCode.$model" :class="getInvalidClass('rtuCode', 'is-invalid', { afterSubmit: false })"
                                                class="form-control" id="rtuCode" name="rtuCode" type="text" placeholder="Cth. RTU00-D7-BAL"
                                                v-tooltip.top="useRtuCodeTooltip('rtuCode')">
                                        </div>
                                        <div class="form-group">
                                            <label for="rtuName">Nama RTU <span class="text-danger">*</span></label>
                                            <input v-model="v$.rtuName.$model" :class="getInvalidClass('rtuName', 'is-invalid')"
                                                class="form-control" id="rtuName" name="rtuName" type="text" placeholder="Cth. RTU STO BALAI KOTA">
                                        </div>
                                        <div class="form-group">
                                            <label for="location">Lokasi <span class="text-danger">*</span></label>
                                            <input v-model="v$.location.$model" :class="getInvalidClass('location', 'is-invalid')"
                                                class="form-control" id="location" name="location" type="text" placeholder="Cth. STO BALAI KOTA">
                                        </div>
                                        <div class="form-group">
                                            <label for="stoCode">Kode STO <span class="text-danger">*</span></label>
                                            <input v-model="v$.stoCode.$model" :class="getInvalidClass('stoCode', 'is-invalid')"
                                                class="form-control" id="stoCode" name="stoCode" type="text" placeholder="Cth. BAL">
                                        </div>
                                        
                                        <InputGroupLocation ref="inputLocation" :divreValue="data.divreCode" :witelValue="data.witelCode"
                                            @change="onLocationChange" />
        
                                        <div class="row mb-4 align-items-end">
                                            <div class="col-md-6 col-lg-auto">
                                                <div class="form-group">
                                                    <label for="portKwh" class="required">Analog Port KW</label>
                                                    <input v-model="v$.portKwh.$model" :class="getInvalidClass('portKwh', 'is-invalid')"
                                                        class="form-control" id="portKwh" name="portKwh" type="text" placeholder="Cth. A-16">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-auto">
                                                <div class="form-group">
                                                    <label for="portGenset" class="required">Digital Port Status Genset</label>
                                                    <input v-model="v$.portGenset.$model" :class="getInvalidClass('portGenset', 'is-invalid')"
                                                        class="form-control" id="portGenset" name="portGenset" type="text" placeholder="Cth. D-02">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg">
                                                <div class="form-group">
                                                    <label for="kvaGenset" class="required">Kapasitas Genset Terpasang (KVA)</label>
                                                    <input v-model="v$.kvaGenset.$model" :class="getInvalidClass('kvaGenset', 'is-invalid')"
                                                        class="form-control" id="kvaGenset" name="kvaGenset" type="text" placeholder="Cth. 500">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-auto">
                                                <div class="form-group">
                                                    <label for="portPue">Analog Port PUE</label>
                                                    <input v-model="v$.portPue.$model" class="form-control" id="portPue" name="portPue"
                                                        type="text" placeholder="Cth. A-92">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-auto">
                                                <div class="form-group">
                                                    <label for="portPue">Analog Port PUE (NewOsase Baru)</label>
                                                    <input v-model="v$.portPueV2.$model" class="form-control" id="portPueV2" name="portPueV2"
                                                        type="text" placeholder="Cth. A-92">
                                                </div>
                                            </div>
                                        </div>
                                        <p>Apakah anda ingin menghubungkan lokasi RTU dengan Lokasi GEPEE?</p>
                                        <div class="px-4">
                                            <div class="row align-items-center">
                                                <div class="col-auto mb-2">
                                                    <InputSwitch v-model="data.useGepee" :disabled="isLoading" inputId="switchUseGepee" />
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
                                    </div>
                                    <div v-if="isRoleAdmin">
                                        <div v-if="showForm" class="d-flex justify-content-end align-items-center px-4 tw-gap-x-8 tw-gap-y-6">
                                            <button type="reset" :disabled="isUpdateLoading" @click="onReset"
                                                class="btn btn-sm btn-danger">Batalkan</button>
                                            <button type="submit" :disabled="isUpdateLoading" :class="{ 'btn-loading': isUpdateLoading }"
                                                class="btn btn-primary btn-lg">Simpan Perubahan</button>
                                        </div>
                                        <div v-else class="d-flex justify-content-end px-4">
                                            <button type="button" @click="showForm = true"
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

.form-section:not(.is-editable) {
    @apply tw-relative after:tw-absolute after:tw-inset-0;
}

.form-section:not(.is-editable) .form-control,
.form-section:not(.is-editable) :deep(.form-control) {
    @apply tw-border-transparent;
}

</style>