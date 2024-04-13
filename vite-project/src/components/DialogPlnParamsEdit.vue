<script setup>
import { ref } from "vue";
import { usePlnStore } from "@/stores/pln";
import { useViewStore } from "@/stores/view";
import { useDataForm } from "@/helpers/data-form";
import { required } from "@vuelidate/validators";
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

const paramsId = props.dataParams.id;
const { data, v$ } = useDataForm({
    kwh1_low: { value: props.dataParams.port_kwh1_low || null, required },
    kwh1_high: { value: props.dataParams.port_kwh1_high || null, required },
    kwh2_low: { value: props.dataParams.port_kwh2_low || null },
    kwh2_high: { value: props.dataParams.port_kwh2_high || null },
    kva: { value: props.dataParams.port_kva || null },
    kw: { value: props.dataParams.port_kw || null },
    power_factor: { value: props.dataParams.port_power_factor || null, required }
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
        port_kwh1_low: data.kwh1_low,
        port_kwh1_high: data.kwh1_high,
        port_kwh2_low: data.kwh2_low,
        port_kwh2_high: data.kwh2_high,
        port_kva: data.kva,
        port_kw: data.kw,
        port_power_factor: data.power_factor
    };

    isLoading.value = true;
    plnStore.updateParams(paramsId, body, response => {
        isLoading.value = false;
        if(!response.success)
            return;
        viewStore.showToast("Parameter PLN", "Berhasil menyimpan perubahan.", true);
        emit("save");
    });
};
</script>
<template>
    <Dialog header="Form Update PLN Parameter" v-model:visible="showDialog" modal draggable @afterHide="onHide" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <form @submit.prevent="onSubmit" class="p-4">
            <div>
                <h5 class="mb-4">Bulan {{ currMonthName }} {{ currYear }}</h5>
            </div>
            <div class="row gx-5">
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKwh1Low">Port KWH 1 Low <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kwh1_low.$model" id="inputKwh1Low" :class="{ 'is-invalid': hasSubmitted && v$.kwh1_low.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKwh1High">Port KWH 1 High <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kwh1_high.$model" id="inputKwh1High" :class="{ 'is-invalid': hasSubmitted && v$.kwh1_high.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKwh2Low">Port KWH 2 Low <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kwh2_low.$model" id="inputKwh2Low" :class="{ 'is-invalid': hasSubmitted && v$.kwh2_low.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKwh2High">Port KWH 2 High <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kwh2_high.$model" id="inputKwh2High" :class="{ 'is-invalid': hasSubmitted && v$.kwh2_high.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKva">Port KVA <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kva.$model" id="inputKva" :class="{ 'is-invalid': hasSubmitted && v$.kva.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKw">Port KW <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.kw.$model" id="inputKw" :class="{ 'is-invalid': hasSubmitted && v$.kw.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPowerFactor">Port Power Factor <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.power_factor.$model" id="inputPowerFactor" :class="{ 'is-invalid': hasSubmitted && v$.power_factor.$invalid }" class="form-control" placeholder="" />
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