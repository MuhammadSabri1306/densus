<script setup>
import { ref, computed } from "vue";
import { usePlnStore } from "@/stores/pln";
import { useViewStore } from "@/stores/view";
import { useDataForm } from "@/helpers/data-form";
import { required, numeric } from "@vuelidate/validators";
import { toIdrCurrency } from "@/helpers/number-format";
import Dialog from "primevue/dialog";
import InputGroupLocation from "@/components/InputGroupLocation.vue";

const emit = defineEmits(["cancel", "save"]);

const showDialog = ref(true);
const isSuccess = ref(false);
const onHide = () => {
    showDialog.value = false;
    if(isSuccess.value)
        emit("save");
    else
        emit("cancel");
};

const { data, v$ } = useDataForm({
    id_pel: { required },
    nama_pel: { required },
    tarif_pel: { value: "B2", required },
    daya_pel: { required, numeric },
    lokasi_pel: {},
    alamat_pel: {},
    gedung: {},
    divre_kode: { required },
    divre_name: { required },
    witel_kode: { required },
    witel_name: { required },
    sto_kode: {},
    sto_name: {},
    tipe: {},
    rtu_kode: {}
});

const dayaPelFormat = computed(() => {
    const dayaPel = data.daya_pel;
    const result = toIdrCurrency(dayaPel);
    return result;
});

const onDayaKeypress = val => {
    val = val.replaceAll(".", "");
    data.daya_pel = Number(val) || null;
};

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divre_kode = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_kode = loc.witel_kode;
    data.witel_name = loc.witel_name;
};

const plnStore = usePlnStore();
const viewStore = useViewStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;

    const isValid = await v$.value.$validate();
    const isLocationValid = inputLocation.value.validate();
    if(!isValid || !isLocationValid)
        return false;
    
    const body = {
        id_pel_pln: data.id_pel,
        nama_pel_pln: data.nama_pel.toUpperCase(),
        tarif_pel_pln: data.tarif_pel,
        daya_pel_pln: data.daya_pel,
        lokasi_pel_pln: data.lokasi_pel?.toUpperCase(),
        alamat_pel_pln: data.alamat_pel?.toUpperCase(),
        gedung: data.gedung?.toUpperCase(),
        divre_kode: data.divre_kode,
        divre_name: data.divre_name,
        witel_kode: data.witel_kode,
        witel_name: data.witel_name,
        sto_kode: data.sto_kode,
        sto_name: data.sto_name?.toUpperCase(),
        tipe: data.tipe?.toUpperCase(),
        rtu_kode: data.rtu_kode
    };

    isLoading.value = true;
    plnStore.createGepeeLocation(body, response => {
        isLoading.value = false;
        if(!response.success)
            return;
        
        viewStore.showToast("Lokasi Gepee", "Berhasil menyimpan lokasi baru.", true);
        isSuccess.value = true;
        showDialog.value = false;
    });
};
</script>
<template>
    <Dialog header="Form Lokasi Gepee Baru" v-model:visible="showDialog" modal draggable @afterHide="onHide" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <form @submit.prevent="onSubmit" class="p-4">
            <div class="row gx-5">
                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label for="inputIdPel">ID Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.id_pel.$model" id="inputIdPel" :class="{ 'is-invalid': hasSubmitted && v$.id_pel.$invalid }" class="form-control" placeholder="Cth: 321100322678" autofocus />
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label for="inputNamaPel">Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" v-model="v$.nama_pel.$model" id="inputNamaPel" :class="{ 'is-invalid': hasSubmitted && v$.nama_pel.$invalid }" class="form-control" placeholder="Cth: KTR WIL 10 TELKOM" />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-4">
                        <label for="inputDayaPel">Daya <span class="text-danger">*</span></label>
                        <input type="text" :value="dayaPelFormat" @keyup="onDayaKeypress($event.target.value)" @change="onDayaKeypress($event.target.value)" id="inputDayaPel" :class="{ 'is-invalid': hasSubmitted && v$.daya_pel.$invalid }" class="form-control" placeholder="Cth: 865000" />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-4">
                        <label for="inputLokasiPel">Lokasi</label>
                        <input type="text" v-model="v$.lokasi_pel.$model" id="inputLokasiPel" :class="{ 'is-invalid': hasSubmitted && v$.lokasi_pel.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-4">
                        <label for="inputGedungPel">Gedung</label>
                        <input type="text" v-model="v$.gedung.$model" id="inputGedungPel" :class="{ 'is-invalid': hasSubmitted && v$.gedung.$invalid }" class="form-control" placeholder="Cth: STO, PLASA" />
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <label>Jenis Tarif <span class="text-danger">*</span></label>
                    <div class="my-2">
                        <div class="ms-4 mb-2">
                            <input type="radio" v-model="data.tarif_pel" value="B2" id="radioTarifB2" class="radio_animated" />
                            <label for="radioTarifB2">B2</label>
                        </div>
                        <div class="ms-4 mb-2">
                            <input type="radio" v-model="data.tarif_pel" value="B3" id="radioTarifB3" class="radio_animated" />
                            <label for="radioTarifB3">B3</label>
                        </div>
                        <div class="ms-4 mb-2">
                            <input type="radio" v-model="data.tarif_pel" value="LB3" id="radioTarifLB3" class="radio_animated" />
                            <label for="radioTarifLB3">LB3</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md">
                    <div class="mb-4">
                        <label for="txtAlamatPel">Alamat</label>
                        <textarea v-model="v$.alamat_pel.$model" id="txtAlamatPel" :class="{ 'is-invalid': hasSubmitted && v$.alamat_pel.$invalid }" class="form-control" rows="5" placeholder=""></textarea>
                    </div>
                </div>
            </div>

            <InputGroupLocation ref="inputLocation" requireDivre requireWitel @change="onLocationChange" />
            
            <div class="row gx-5">
                <div class="col-12 col-md-4">
                    <div class="mb-4">
                        <label for="inputStoCode">Kode STO</label>
                        <input type="text" v-model="v$.sto_kode.$model" id="inputStoCode" :class="{ 'is-invalid': hasSubmitted && v$.sto_kode.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="mb-4">
                        <label for="inputStoName">Nama STO</label>
                        <input type="text" v-model="v$.sto_name.$model" id="inputStoName" :class="{ 'is-invalid': hasSubmitted && v$.sto_name.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="mb-4">
                        <label for="inputTipe">Tipe</label>
                        <input type="text" v-model="v$.tipe.$model" id="inputTipe" :class="{ 'is-invalid': hasSubmitted && v$.tipe.$invalid }" class="form-control" placeholder="Cth: STO, MIX, OFFICE" />
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="mb-4">
                        <label for="inputRtuCode">Kode RTU</label>
                        <input type="text" v-model="v$.rtu_kode.$model" id="inputRtuCode" :class="{ 'is-invalid': hasSubmitted && v$.rtu_kode.$invalid }" class="form-control" placeholder="" />
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-end mt-5">
                <button type="button" @click="showDialog = false" class="btn btn-secondary">Batalkan</button>
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-success btn-lg">Simpan</button>
            </div>
        </form>
    </Dialog>
</template>