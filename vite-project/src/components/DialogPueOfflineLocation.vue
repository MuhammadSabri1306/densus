<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePueV2Store } from "@stores/pue-v2";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import InputGroupLocation from "@components/InputGroupLocation.vue";

defineEmits(["close", "save"]);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    divreCode: { required },
    divreName: { required },
    witelCode: { required },
    witelName: { required },
    location: { required }
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divreCode = loc.divre_kode;
    data.divreName = loc.divre_name;
    data.witelCode = loc.witel_kode;
    data.witelName = loc.witel_name;
};

const pueStore = usePueV2Store();
const viewStore = useViewStore();
const route = useRoute();
const idLocation = computed(() => route.params.locationId);

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    const isLocationValid = inputLocation.value.validate();

    if(!isValid || !isLocationValid)
        return;
    
    const body = {
        divre_kode: data.divreCode,
        divre_name: data.divreName,
        witel_kode: data.witelCode,
        witel_name: data.witelName,
        lokasi: data.location
    };
    
    isLoading.value = true;
    if(idLocation.value) {

        pueStore.updateOfflineLocation(body, response => {
            if(response.status)
                viewStore.showToast("Lokasi PUE Offline", "Berhasil menyimpan perubahan lokasi.", true);
            isLoading.value = false;
        });
        
    } else {
        
        pueStore.createOfflineLocation(body, response => {
            if(response.status)
                viewStore.showToast("Lokasi PUE Offline", "Berhasil menambahkan lokasi.", true);
            isLoading.value = false;
        });

    }
};
</script>
<template>
    <Dialog header="Form Input Lokasi PUE Offline" v-model:visible="showDialog"
        maximizable modal draggable @afterHide="$emit('close')" contentClass="tw-overflow-y-visible">
        <div class="p-4">
            <form @submit.prevent="onSubmit">
                <div class="form-group">
                    <label for="location" class="required">Lokasi</label>
                    <input v-model="v$.location.$model" :class="{ 'is-invalid': hasSubmitted && v$.location.$invalid }" class="form-control" id="location" type="text" placeholder="Masukkan lokasi">
                </div>
                <InputGroupLocation ref="inputLocation" :divreValue="data.divreCode"
                    :witelValue="data.witelCode" @change="onLocationChange" class="mb-4" />
                <div class="d-flex justify-content-between align-items-end">
                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>