<script setup>
import { ref, computed } from "vue";
import { useDataForm } from "@/helpers/data-form";
import { required, numeric } from "@vuelidate/validators";
import Steps from "@/components/ui/Steps.vue";
import InputGroupLocation from "@/components/InputGroupLocation.vue";

const emit = defineEmits(["update"]);
const props = defineProps({
    stepsPage: { type: Array, default: [] },
    useValidation: { type: Boolean, default: false }
});

const { data, v$ } = useDataForm({
    rtu_kode: { required },
    rtu_name: { required },
    lokasi: { required },
    sto_kode: { required },
    alamat: { required },
    divre_kode: { required },
    divre_name: { required },
    witel_kode: { required },
    witel_name: { required },
    daya: { required, numeric }
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divre_kode = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_kode = loc.witel_kode;
    data.witel_name = loc.witel_name;
};

const hasSubmitted = ref(false);
const onNavigate = async (pageNumber) => {
    hasSubmitted.value = true;

    const isValid = await v$.value.$validate();
    const isLocationValid = inputLocation.value.validate();
    if((props.useValidation && !isValid) || (props.useValidation && !isLocationValid))
        return false;
    
    emit("update", {
        navigateTo: pageNumber,
        data: {
            divre_kode: data.divre_kode,
            divre_name: data.divre_name,
            witel_kode: data.witel_kode,
            witel_name: data.witel_name,
            sto_kode: data.sto_kode,
            lokasi: data.lokasi,
            alamat: data.alamat,
            rtu_kode: data.rtu_kode,
            rtu_name: data.rtu_name,
            daya: data.daya
        }
    });
};
</script>
<template>
    <div class="row gx-5">
        <div class="col-12">
            <Steps :steps="stepsPage" :currPage="1" @navigate="onNavigate" class="mb-4" aria-label="Form Input PLN Parameter" />
        </div>
    </div>
    <InputGroupLocation ref="inputLocation" requireDivre requireWitel @change="onLocationChange" class="gx-5" />
    <div class="row gx-5">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label for="inputStoCode" class="required">Kode STO</label>
                <input type="text" v-model="v$.sto_kode.$model" id="inputStoCode" :class="{ 'is-invalid': hasSubmitted && v$.sto_kode.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-12 col-md-8">
            <div class="mb-4">
                <label for="inputLokasi" class="required">Lokasi</label>
                <input type="text" v-model="v$.lokasi.$model" id="inputLokasi" :class="{ 'is-invalid': hasSubmitted && v$.lokasi.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-12">
            <div class="mb-4">
                <label for="textAlamat" class="required">Alamat</label>
                <textarea v-model="v$.alamat.$model" id="textAlamat" :class="{ 'is-invalid': hasSubmitted && v$.alamat.$invalid }" class="form-control" rows="3" placeholder=""></textarea>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="mb-4">
                <label for="inputRtuCode" class="required">Kode RTU</label>
                <input type="text" v-model="v$.rtu_kode.$model" id="inputRtuCode" :class="{ 'is-invalid': hasSubmitted && v$.rtu_kode.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="mb-4">
                <label for="inputRtuName" class="required">Nama RTU</label>
                <input type="text" v-model="v$.rtu_name.$model" id="inputRtuName" :class="{ 'is-invalid': hasSubmitted && v$.rtu_name.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="mb-4">
                <label for="inputDaya" class="required">Daya</label>
                <input type="text" v-model="v$.daya.$model" id="inputDaya" :class="{ 'is-invalid': hasSubmitted && v$.daya.$invalid }" class="form-control" placeholder="" />
            </div>
        </div>
    </div>
</template>