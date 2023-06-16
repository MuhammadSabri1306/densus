<script setup>
import { ref } from "vue";
import { usePueTargetStore } from "@stores/pue-target";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import { required, integer } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import InputGroupLocation from "@components/InputGroupLocation.vue";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    initData: { type: Object, required: true }
});
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    divre_kode: { value: props.initData.divre_kode, required },
    divre_name: { value: props.initData.divre_name, required },
    witel_kode: { value: props.initData.witel_kode, required },
    witel_name: { value: props.initData.witel_name, required },
    target: { required, integer },
    quartal: { value: props.initData.quartal, required }
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
        target: data.target,
        quartal: data.quartal
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
        <div class="py-4 py-md-0">
            <form @submit.prevent="onSubmit">
                <div class="mb-5 card card-body bg-primary p-4">
                    <h5 class="mb-2">Quartal ke-{{ data.quartal }}</h5>
                    <p class="mb-1"><b>{{ data.witel_name }}</b></p>
                    <p class="mb-0">{{ data.divre_name }}</p>
                </div>
                <div class="row px-4 mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-5">
                            <label for="inputTarget" class="required">Target Lokasi PUE</label>
                            <input type="text" v-model="v$.target.$model" :class="{ 'is-invalid': hasSubmitted && v$.target.$invalid }"
                                class="form-control form-control-lg" id="inputTarget" placeholder="Cth. 5">
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