<script setup>
import { ref } from "vue";
import { usePueTargetStore } from "@stores/pue-target";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import { required, integer } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import InputGroupLocation from "@components/InputGroupLocation.vue";

const emit = defineEmits(["close", "save"]);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    divre_kode: { required },
    divre_name: { required },
    witel_kode: { required },
    witel_name: { required },
    target: { required, integer }
});

const pueTargetStore = usePueTargetStore();
const viewStore = useViewStore();

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        divre_kode: data.divre_kode,
        divre_name: data.divre_name,
        witel_kode: data.witel_kode,
        witel_name: data.witel_name,
        target: data.target
    };
    
    isLoading.value = true;
    pueTargetStore.create(body, response => {
        if(response.success) {
            viewStore.showToast("Target PUE", "Berhasil menambahkan item.", true);
            emit("save");
            showDialog.value = false;
        }
        isLoading.value = false;
    });
};

const inputLocation = ref(null);
const onLocationChange = loc => {
    data.divre_kode = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_kode = loc.witel_kode;
    data.witel_name = loc.witel_name;
};
</script>
<template>
    <Dialog header="Form Input Target PUE" v-model:visible="showDialog" maximizable modal draggable
        @afterHide="$emit('close')" class="dialog-basic" contentClass="tw-overflow-y-visible">
        <div class="p-4">
            <form @submit.prevent="onSubmit">
                <InputGroupLocation ref="inputLocation" requireDivre requireWitel locationType="gepee"
                    @change="onLocationChange" />
                <div class="row px-4 mb-5">
                    <div class="col-md-6">
                        <div class="form-group mb-5">
                            <label for="inputTarget" class="required">Target Lokasi PUE</label>
                            <input type="text" v-model="v$.target.$model" :class="{ 'is-invalid': hasSubmitted && v$.target.$invalid }"
                                class="form-control" id="inputTarget" placeholder="Cth. 5">
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