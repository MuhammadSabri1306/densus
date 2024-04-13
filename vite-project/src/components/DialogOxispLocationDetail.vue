<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useOxispStore } from "@/stores/oxisp";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import Dialog from "primevue/dialog";
import Skeleton from "primevue/skeleton";
import InputGroupLocation from "@/components/InputGroupLocation.vue";

const emit = defineEmits(["update", "close"]);
const showDialog = ref(true);

const route = useRoute();
const idLocation = computed(() => route.params.idLocation);

const userStore = useUserStore();
const isAdmin = computed(() => userStore.role == "admin");

const { data, v$ } = useDataForm({
    divre_kode: { required },
    divre_name: { required },
    witel_kode: { required },
    witel_name: { required },
    datel: { required },
    sto_kode: { required },
    sto_name: { required }
});

const oxispStore = useOxispStore();
const isLocFetched = ref(false);
oxispStore.getLocation(idLocation.value, response => {
    if(response.data.location) {

        data.divre_kode = response.data.location.divre_kode;
        data.divre_name = response.data.location.divre_name;
        data.witel_kode = response.data.location.witel_kode;
        data.witel_name = response.data.location.witel_name;
        data.datel = response.data.location.datel;
        data.sto_kode = response.data.location.sto_kode;
        data.sto_name = response.data.location.sto_name;
    
    }
    isLocFetched.value = true;
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divre_kode = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_kode = loc.witel_kode;
    data.witel_name = loc.witel_name;
};

const useEdit = ref(false);

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
    oxispStore.updateLocation(idLocation.value, body, response => {
        isLoading.value = false;
        if(response.success) {
            viewStore.showToast("Lokasi OXISP", "Berhasil menyimpan perubahan.", true);
            emit("update");
            useEdit.value = false;
        }
    });
};

const isDeleteLoading = ref(false);
const onDelete = () => {
    if(!confirm("Anda akan menghapus Lokasi. Lanjutkan?"))
        return false;
    
    isDeleteLoading.value = true;
    oxispStore.deleteLocation(idLocation.value, response => {
        if(!response.success)
            return;
        
        viewStore.showToast("Lokasi OXISP", "Berhasil menghapus lokasi.", true);
        isDeleteLoading.value = false;
        emit("update");
        showDialog.value = false;
    });
};
</script>
<template>
    <Dialog header="Detail Lokasi OXISP" v-model:visible="showDialog" modal maximizable draggable
        class="dialog-basic" @afterHide="$router.push('/oxisp/activity')">
        <div class="pt-4 pt-md-0">
            <div v-if="isLocFetched && !useEdit && isAdmin" class="row justify-content-end g-4">
                <div class="col-auto">
                    <button type="button" @click="useEdit = true" class="btn btn-secondary btn-icon">
                        <VueFeather type="edit-2" size="1.2em" />
                        <span class="ms-2">Edit</span>
                    </button>
                </div>
                <div class="col-auto">
                    <button type="button" @click="onDelete" class="btn btn-danger btn-icon">
                        <VueFeather type="trash" size="1.2em" />
                        <span class="ms-2">Hapus</span>
                    </button>
                </div>
            </div>
            <div v-if="!isLocFetched" class="mt-4 p-4">
                <div class="row gx-4 gy-5">
                    <div class="col-md-6"><Skeleton /></div>
                    <div class="col-md-6"><Skeleton /></div>
                    <div class="col-md-7 col-lg-4"><Skeleton /></div>
                    <div class="col-md-5 col-lg-3"><Skeleton /></div>
                    <div class="col-md-8 col-lg-5"><Skeleton /></div>
                </div>
            </div>
            <div v-if="isLocFetched && !useEdit" class="py-4">
                <div class="mb-4 px-4">
                    <table class="table table-bordered">
                        <tr>
                            <td>Regional</td>
                            <td>{{ data.divre_name }}</td>
                        </tr>
                        <tr>
                            <td>Kode Regional</td>
                            <td>{{ data.divre_kode }}</td>
                        </tr>
                        <tr>
                            <td>Witel</td>
                            <td>{{ data.witel_name }}</td>
                        </tr>
                        <tr>
                            <td>Kode Witel</td>
                            <td>{{ data.witel_kode }}</td>
                        </tr>
                        <tr>
                            <td>Datel</td>
                            <td>{{ data.datel }}</td>
                        </tr>
                        <tr>
                            <td>STO</td>
                            <td>{{ data.sto_kode }} / {{ data.sto_name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="d-flex justify-content-end align-items-end">
                    <button type="button" @click="showDialog = false" class="btn btn-info">Tutup</button>
                </div>
            </div>
            <form v-else-if="isLocFetched" @submit.prevent="onSubmit" class="p-4">
                <InputGroupLocation ref="inputLocation" :divreValue="data.divre_kode" :witelValue="data.witel_kode"
                    requireDivre requireWitel position="bottom" @change="onLocationChange" class="mb-4" />
                <div class="row align-items-end mb-5">
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
                    <button type="button" @click="useEdit = false" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>
<style scoped>

.form-location .form-control[readonly] {
    border-color: transparent;
    padding-left: 0;
    padding-right: 0;
}

</style>