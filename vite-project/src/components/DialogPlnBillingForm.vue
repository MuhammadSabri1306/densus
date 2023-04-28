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
    billing: { type: Object, required: true }
});

const showDialog = ref(true);
const onHide = () => {
    showDialog.value = false;
    emit("close");
};

const route = useRoute();
const locationId = computed(() => route.params.locationId);

const billingId = props.billing.id;
const { data, v$ } = useDataForm({
    lwbp_awal: { value: props.billing.lwbp_awal || null, required, numeric },
    lwbp_akhir: { value: props.billing.lwbp_akhir || null, required, numeric },
    wbp_awal: { value: props.billing.wbp_awal || null, numeric },
    wbp_akhir: { value: props.billing.wbp_akhir || null, numeric },
    kvarh_awal: { value: props.billing.kvarh_awal || null, numeric },
    kvarh_akhir: { value: props.billing.kvarh_akhir || null, numeric },
    fkm: { value: props.billing.fkm || null, required, numeric }
});

const viewStore = useViewStore();
const currDate = new Date();
const currYear = currDate.getFullYear();
const currMonthName = viewStore.monthList[currDate.getMonth()]?.name;

const plnStore = usePlnStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);
const isSuccess = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return false;
    
    const body = {
        lwbp_awal: data.lwbp_awal,
        lwbp_akhir: data.lwbp_akhir,
        wbp_awal: data.wbp_awal,
        wbp_akhir: data.wbp_akhir,
        kvarh_awal: data.kvarh_awal,
        kvarh_akhir: data.kvarh_akhir,
        fkm: data.fkm
    };

    isLoading.value = true;
    if(billingId) {

        plnStore.updateBilling(billingId, body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Billing PLN", "Berhasil menyimpan perubahan.", true);
            emit("save");
        });

    } else {

        body['id_location'] = locationId.value;
        plnStore.createBilling(body, response => {
            isLoading.value = false;
            if(!response.success)
                return;
            viewStore.showToast("Billing PLN", "Berhasil menyimpan data bulan ini.", true);
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
                        <label for="inputLwbpAwal">LWBP Awal <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.lwbp_awal.$model" id="inputLwbpAwal" :class="{ 'is-invalid': hasSubmitted && v$.lwbp_awal.$invalid }" class="form-control" placeholder="Cth: 18246.86" autofocus />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputLwbpAkhir">LWBP Akhir <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.lwbp_akhir.$model" id="inputLwbpAkhir" :class="{ 'is-invalid': hasSubmitted && v$.lwbp_akhir.$invalid }" class="form-control" placeholder="Cth: 18524.74" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputWbpAwal">WBP Awal</label>
                        <input type="text" v-model="v$.wbp_awal.$model" id="inputWbpAwal" class="form-control" placeholder="Cth: 3663.4" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputWbpAkhir">WBP Akhir</label>
                        <input type="text" v-model="v$.wbp_akhir.$model" id="inputWbpAkhir" class="form-control" placeholder="Cth: 3718.33" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKvarhAwal">kVARh Awal</label>
                        <input type="text" v-model="v$.kvarh_awal.$model" id="inputKvarhAwal" class="form-control" placeholder="Cth: 6616.89" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputKvarhAkhir">kVARh Akhir</label>
                        <input type="text" v-model="v$.kvarh_akhir.$model" id="inputKvarhAkhir" class="form-control" placeholder="Cth: 6715.23" />
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-4">
                        <label for="inputFkm">Faktor Kali Meter <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.fkm.$model" id="inputFkm" :class="{ 'is-invalid': hasSubmitted && v$.fkm.$invalid }" class="form-control" placeholder="Cth: 800" />
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