<script setup>
import { ref } from "vue";
import { useDataForm } from "@/helpers/data-form";
import { required } from "@vuelidate/validators";
import Steps from "@/components/ui/Steps.vue";

const emit = defineEmits(["update"]);
const props = defineProps({
    stepsPage: { type: Array, default: [] },
    useValidation: { type: Boolean, default: false }
});

const { data, v$ } = useDataForm({
    port_deg1_low: { value: props.dataParams.port_deg1_low || null, required },
    port_deg1_high: { value: props.dataParams.port_deg1_high || null, required },
    port_deg2_low: { value: props.dataParams.port_deg2_low || null, required },
    port_deg2_high: { value: props.dataParams.port_deg2_high || null, required },
    port_status_genset: { value: props.dataParams.port_status_genset || null, required },
    port_status_pln: { value: props.dataParams.port_status_pln || null, required }
});

const hasSubmitted = ref(false);
const onNavigate = async (pageNumber) => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(props.useValidation && !isValid)
        return false;
    
    emit("update", {
        navigateTo: pageNumber,
        data: {
            port_deg1_low: data.port_deg1_low,
            port_deg1_high: data.port_deg1_high,
            port_deg2_low: data.port_deg2_low,
            port_deg2_high: data.port_deg2_high,
            port_status_genset: data.port_status_genset,
            port_status_pln: data.port_status_pln
        }
    });
};
</script>
<template>
    <div class="row gx-5">
        <div class="col-12">
            <Steps :steps="stepsPage" :currPage="2" @navigate="onNavigate" class="mb-4" aria-label="Form Input PLN Parameter" />
        </div>
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
</template>