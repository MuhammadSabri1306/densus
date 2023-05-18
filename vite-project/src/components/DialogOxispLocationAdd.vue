<script setup>
import { ref } from "vue";
import { useOxispStore } from "@stores/oxisp";
import { useViewStore } from "@stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import Dialog from "primevue/dialog";
import InputGroupLocation from "@components/InputGroupLocation.vue";

const emit = defineEmits(["save", "close"]);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    divre_kode: { required },
    divre_name: { required },
    witel_kode: { required },
    witel_name: { required },
    datel: { required },
    sto_kode: { required },
    sto_name: { required }
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divre_kode = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_kode = loc.witel_kode;
    data.witel_name = loc.witel_name;
};

const oxispStore = useOxispStore();
const viewStore = useViewStore();
const hasSubmitted = ref(false);
const isLoading = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    const isLocationValid = inputLocation.value.validate();
    
    if(!isValid || !isLocationValid)
        return;
    
    const body = {
        divre_kode: data.divre_kode,
        divre_name: data.divre_name,
        witel_kode: data.witel_kode,
        witel_name: data.witel_name,
        datel: data.datel,
        sto_kode: data.sto_kode,
        sto_name: data.sto_name
    };

    isLoading.value = true;
    oxispStore.createLocation(body, response => {
        isLoading.value = false;
        if(response.success) {
            viewStore.showToast("Lokasi OXISP", "Berhasil menyimpan lokasi baru.", true);
            emit("save");
            showDialog.value = false;
        }
    });
};
</script>
<template>
    <Dialog header="Input Lokasi OXISP" v-model:visible="showDialog" modal maximizable draggable
        :style="{ width: '40vw' }" :breakpoints="{ '960px': '60vw', '641px': '100vw' }"
        @afterHide="$emit('close')">
        <div class="p-4">
            <form @submit.prevent="onSubmit">
                <InputGroupLocation ref="inputLocation" requireDivre requireWitel position="bottom"
                    @change="onLocationChange" class="mb-4" />
                <div class="row mb-5">
                    <div class="col-md-7 col-lg-4">
                        <div class="form-group">
                            <label for="inputDatel" class="required">Datel</label>
                            <input v-model="v$.datel.$model" :class="{ 'is-invalid': hasSubmitted && v$.datel.$invalid }"
                                class="form-control" id="inputDatel" type="text" placeholder="Cth. MAKASSAR">
                        </div>
                    </div>
                    <div class="col-md-5 col-lg-3">
                        <div class="form-group">
                            <label for="inputStoCode" class="required">Kode STO</label>
                            <input v-model="v$.sto_kode.$model" :class="{ 'is-invalid': hasSubmitted && v$.sto_kode.$invalid }"
                                class="form-control" id="inputStoCode" type="text" placeholder="Cth. BAL">
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-5">
                        <div class="form-group">
                            <label for="inputStoName" class="required">Nama STO</label>
                            <input v-model="v$.sto_name.$model" :class="{ 'is-invalid': hasSubmitted && v$.sto_name.$invalid }"
                                class="form-control" id="inputStoName" type="text" placeholder="Cth. BALAIKOTA">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>