<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePlnStore } from "@stores/pln";
import { useViewStore } from "@stores/view";
import { useDataForm } from "@helpers/data-form";
import { required, numeric } from "@vuelidate/validators";
import Dialog from "primevue/dialog";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    dataParams: { type: Object, required: true }
});

const showDialog = ref(true);
const onHide = () => {
    showDialog.value = false;
    emit("close");
};

const route = useRoute();
const locationId = computed(() => route.params.locationId);

const paramsId = props.dataParams.id;
const { data, v$ } = useDataForm({
    port_deg1_low: { value: props.dataParams.port_deg1_low || null, required, numeric },
    port_deg1_high: { value: props.dataParams.port_deg1_high || null, required, numeric },
    port_deg2_low: { value: props.dataParams.port_deg2_low || null, required, numeric },
    port_deg2_high: { value: props.dataParams.port_deg2_high || null, required, numeric },
    port_status_genset: { value: props.dataParams.port_status_genset || null, required, numeric },
    port_status_pln: { value: props.dataParams.port_status_pln || null, required, numeric }
});

const viewStore = useViewStore();
const currDate = new Date();
const currYear = currDate.getFullYear();
const currMonthName = viewStore.monthList[currDate.getMonth()]?.name;

const plnStore = usePlnStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return false;
    
    const body = {
        port_deg1_low: data.port_deg1_low,
        port_deg1_high: data.port_deg1_high,
        port_deg2_low: data.port_deg2_low,
        port_deg2_high: data.port_deg2_high,
        port_status_genset: data.port_status_genset,
        port_status_pln: data.port_status_pln
    };

    isLoading.value = true;
    if(paramsId) {

        plnStore.updateParams(paramsId, body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Fuel Parameter", "Berhasil menyimpan perubahan.", true);
            emit("save");
        });

    } else {

        body['id_lokasi_gepee'] = locationId.value;
        plnStore.createParams(body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Fuel Parameter", "Berhasil menyimpan data bulan ini.", true);
            emit("save");
        });

    }

};
</script>
<template>
    <Dialog header="Form Billing PLN" v-model:visible="showDialog" modal draggable @afterHide="onHide" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <form @submit.prevent="onSubmit" class="p-4">
            <div>
                <h5 class="mb-4">Bulan {{ currMonthName }} {{ currYear }}</h5>
            </div>
            <div class="row gx-5">
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortDeg1Low">Port DEG 1 (Low) <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_deg1_low.$model" id="inputPortDeg1Low" :class="{ 'is-invalid': hasSubmitted && v$.port_deg1_low.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortDeg1High">Port DEG 1 (High) <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_deg1_high.$model" id="inputPortDeg1High" :class="{ 'is-invalid': hasSubmitted && v$.port_deg1_high.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortDeg2Low">Port DEG 2 (Low) <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_deg2_low.$model" id="inputPortDeg2Low" :class="{ 'is-invalid': hasSubmitted && v$.port_deg2_low.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortDeg2High">Port DEG 2 (High) <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_deg2_high.$model" id="inputPortDeg2High" :class="{ 'is-invalid': hasSubmitted && v$.port_deg2_high.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortStatusGenset">Port Status Digital Genset <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_status_genset.$model" id="inputPortStatusGenset" :class="{ 'is-invalid': hasSubmitted && v$.port_status_genset.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPortStatusPln">Port Status Digital PLN <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.port_status_pln.$model" id="inputPortStatusPln" :class="{ 'is-invalid': hasSubmitted && v$.port_status_pln.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end mt-5">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-success btn-lg">Simpan</button>
                <button type="button" @click="showDialog = false" class="btn btn-secondary">Batalkan</button>
            </div>
        </form>
    </Dialog>
</template>