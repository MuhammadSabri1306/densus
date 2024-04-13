<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useFuelStore } from "@/stores/fuel";
import { useViewStore } from "@/stores/view";
import { useDataForm } from "@/helpers/data-form";
import { required, numeric } from "@vuelidate/validators";
import Dialog from "primevue/dialog";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    invoice: { type: Object, required: true }
});

const showDialog = ref(true);
const onHide = () => {
    showDialog.value = false;
    emit("close");
};

const route = useRoute();
const locationId = computed(() => route.params.locationId);

const invoiceId = props.invoice.id;
const { data, v$ } = useDataForm({
    harga: { value: props.invoice.harga || null, required, numeric },
    ppn: { value: props.invoice.ppn || null, required, numeric },
    pph: { value: props.invoice.pph || null, required, numeric },
    ppbkb: { value: props.invoice.ppbkb || null, required, numeric },
    jumlah: { value: props.invoice.jumlah || null, required, numeric }
});

const viewStore = useViewStore();
const currDate = new Date();
const currYear = currDate.getFullYear();
const currMonthName = viewStore.monthList[currDate.getMonth()]?.name;

const fuelStore = useFuelStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);
const isSuccess = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return false;
    
    const body = {
        harga: data.harga,
        ppn: data.ppn,
        pph: data.pph,
        ppbkb: data.ppbkb,
        jumlah: data.jumlah
    };

    isLoading.value = true;
    if(invoiceId) {

        fuelStore.updateInvoice(invoiceId, body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Fuel Invoice", "Berhasil menyimpan perubahan.", true);
            emit("save");
        });

    } else {

        body['id_location'] = locationId.value;
        fuelStore.createInvoice(body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Fuel Invoice", "Berhasil menyimpan data bulan ini.", true);
            emit("save");
        });

    }

};
</script>
<template>
    <Dialog header="Form Fuel Invoice" v-model:visible="showDialog" modal draggable @afterHide="onHide" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <form @submit.prevent="onSubmit" class="p-4">
            <div>
                <h5 class="mb-4">Bulan {{ currMonthName }} {{ currYear }}</h5>
            </div>
            <div class="row gx-5">
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputHargaAwal">Harga Solar/liter <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.harga.$model" id="inputHargaAwal" :class="{ 'is-invalid': hasSubmitted && v$.harga.$invalid }" class="form-control" placeholder="" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPpn">PPN <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.ppn.$model" id="inputPpn" :class="{ 'is-invalid': hasSubmitted && v$.ppn.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPph">PPH <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.pph.$model" id="inputPph" :class="{ 'is-invalid': hasSubmitted && v$.pph$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputPpbkb">PPBKB <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.ppbkb.$model" id="inputPpbkb" :class="{ 'is-invalid': hasSubmitted && v$.ppbkb.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputJumlah">Jumlah <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.jumlah.$model" id="inputJumlah" :class="{ 'is-invalid': hasSubmitted && v$.jumlah$invalid }" class="form-control" placeholder="" />
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