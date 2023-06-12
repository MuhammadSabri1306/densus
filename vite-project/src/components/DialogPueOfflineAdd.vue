<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePueV2Store } from "@stores/pue-v2";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import { required, integer, decimal } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "@components/ui/FileUpload.vue";
import FormLabel from "@components/ui/FormLabel.vue";
import InputTextNumber from "@components/ui/InputTextNumber.vue";

const emit = defineEmits(["close", "save"]);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    daya_sdp_a: { required, integer },
    daya_sdp_b: { required, integer },
    daya_sdp_c: { required, integer },
    power_factor_sdp: { decimal },
    daya_eq_a: { required, integer },
    daya_eq_b: { required, integer },
    daya_eq_c: { integer },
    evidence: { required },
});

const pueStore = usePueV2Store();
const viewStore = useViewStore();
const route = useRoute();
const idLocation = computed(() => route.params.locationId);

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        id_location: idLocation.value,
        daya_sdp_a: data.daya_sdp_a,
        daya_sdp_b: data.daya_sdp_b,
        daya_sdp_c: data.daya_sdp_c,
        power_factor_sdp: data.power_factor_sdp,
        daya_eq_a: data.daya_eq_a,
        daya_eq_b: data.daya_eq_b,
        daya_eq_c: data.daya_eq_c,
        evidence: data.evidence,
    };
    
    isLoading.value = true;
    pueStore.createOffline(body, response => {
        if(response.success) {
            viewStore.showToast("PUE Offline", "Berhasil menambahkan item.", true);
            emit("save");
            showDialog.value = false;
        }
        isLoading.value = false;
    });
};

const onFileUploaded = event => {
    data.evidence = event.latestUpload;
};

const onFileRemoved = event => {
    data.evidence = event.latestUpload;
};
</script>
<template>
    <Dialog header="Form Input Lokasi PUE Offline" v-model:visible="showDialog" maximizable
        modal draggable class="dialog-basic" contentClass="tw-overflow-x-hidden" @afterHide="$emit('close')">
        <div class="py-4 px-md-4">
            <form @submit.prevent="onSubmit">
                <h6 class="mb-4">Daya Essential Facility</h6>
                <div class="ps-3">
                    <div class="row align-items-end gx-5 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <FormLabel for="dayaSdpA" isRequired>
                                    <template #text>Daya Total Air Conditioner (AC) Essential</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber v-model="v$.daya_sdp_a.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_sdp_a.$invalid }"
                                    class="form-control" id="dayaSdpA" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <FormLabel for="dayaSdpB" isRequired>
                                    <template #text>Daya Total Lampu & Exhaust Fan Essential</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber type="text" v-model="v$.daya_sdp_b.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_sdp_b.$invalid }"
                                    class="form-control" id="dayaSdpB" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <FormLabel for="dayaSdpC" isRequired>
                                    <template #text>Daya Total Rectifier & Inverter Essential</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber type="text" v-model="v$.daya_sdp_c.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_sdp_c.$invalid }"
                                    class="form-control" id="dayaSdpC" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="powerFactorSdp">Power Factor Air Conditioner Essential (Cos Phi)</label>
                                <InputTextNumber type="text" v-model="v$.power_factor_sdp.$model" :class="{ 'is-invalid': hasSubmitted && v$.power_factor_sdp.$invalid }"
                                    class="form-control" id="powerFactorSdp" placeholder="Cth. 0.9" />
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="mb-4">Daya ICT Equipment</h6>
                <div class="ps-3">
                    <div class="row align-items-end gx-5 mb-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <FormLabel for="dayaEqA" isRequired>
                                    <template #text>Daya Total Beban Output DC Rectifier Source A</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber type="text" v-model="v$.daya_eq_a.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_eq_a.$invalid }"
                                    class="form-control" id="dayaEqA" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <FormLabel for="dayaEqB" isRequired>
                                    <template #text>Daya Total Beban Output DC Rectifier Source B</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber type="text" v-model="v$.daya_eq_b.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_eq_b.$invalid }"
                                    class="form-control" id="dayaEqB" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <FormLabel for="dayaEqC">
                                    <template #text>Daya ICT Equipment Lain</template>
                                    <template #info>Nilai daya dalam satuan Watt (tanpa koma)</template>
                                </FormLabel>
                                <InputTextNumber type="text" v-model="v$.daya_eq_c.$model" :class="{ 'is-invalid': hasSubmitted && v$.daya_eq_c.$invalid }"
                                    class="form-control" id="dayaEqC" placeholder="Cth. 345.000" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <FileUpload isRequired url="/attachment/pue/offline" label="File Evidence" accept=".jpg, .jpeg, .png, .pdf" :hasSubmitted="hasSubmitted"
                        acceptText="(*.jpg, *.jpeg, *.png, *.pdf)" @uploaded="onFileUploaded" @removed="onFileRemoved" class="mb-5" />
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>