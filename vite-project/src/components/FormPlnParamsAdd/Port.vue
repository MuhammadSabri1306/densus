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
    kwh1_low: { required },
    kwh1_high: { required },
    kwh2_low: {},
    kwh2_high: {},
    kva: {},
    kw: {},
    power_factor: { required }
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
            port_kwh1_low: data.kwh1_low,
            port_kwh1_high: data.kwh1_high,
            port_kwh2_low: data.kwh2_low,
            port_kwh2_high: data.kwh2_high,
            port_kva: data.kva,
            port_kw: data.kw,
            port_power_factor: data.power_factor
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
                <label for="inputKwh1Low" class="required">Port KWH 1 Low</label>
                <input type="text" v-model="v$.kwh1_low.$model" id="inputKwh1Low" :class="{ 'is-invalid': hasSubmitted && v$.kwh1_low.$invalid }" class="form-control" placeholder="" autofocus />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputKwh1High" class="required">Port KWH 1 High</label>
                <input type="text" v-model="v$.kwh1_high.$model" id="inputKwh1High" :class="{ 'is-invalid': hasSubmitted && v$.kwh1_high.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputKwh2Low">Port KWH 2 Low</label>
                <input type="text" v-model="v$.kwh2_low.$model" id="inputKwh2Low" :class="{ 'is-invalid': hasSubmitted && v$.kwh2_low.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputKwh2High">Port KWH 2 High</label>
                <input type="text" v-model="v$.kwh2_high.$model" id="inputKwh2High" :class="{ 'is-invalid': hasSubmitted && v$.kwh2_high.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputKva">Port KVA</label>
                <input type="text" v-model="v$.kva.$model" id="inputKva" :class="{ 'is-invalid': hasSubmitted && v$.kva.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputKw">Port KW</label>
                <input type="text" v-model="v$.kw.$model" id="inputKw" :class="{ 'is-invalid': hasSubmitted && v$.kw.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-6">
            <div class="mb-4">
                <label for="inputPowerFactor" class="required">Port Power Factor</label>
                <input type="text" v-model="v$.power_factor.$model" id="inputPowerFactor" :class="{ 'is-invalid': hasSubmitted && v$.power_factor.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
    </div>
</template>