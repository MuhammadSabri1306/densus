<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { useListUserStore } from "@/stores/list-user";
import { useDataForm } from "@/helpers/data-form";
import { required } from "@vuelidate/validators";
import Dialog from "primevue/dialog";
import InputGroupLocation from "@/components/InputGroupLocation.vue";

const emit = defineEmits(["saved", "die"]);
const props = defineProps({
    data: Object,
    isCurrUser: { type: Boolean, default: false }
});

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    id: { value: props.data.id, required },
    nama: { value: props.data.nama, required },
    organisasi: { value: props.data.organisasi, required },
    role: { value: props.data.role, required },
    no_hp: { value: props.data.no_hp, required },
    email: { value: props.data.email, required },
    telegram_id: { value: props.data.telegram_id },
    telegram_username: { value: props.data.telegram_username },
    witel_code: { value: props.data.witel_code },
    witel_name: { value: props.data.witel_name },
    divre_code: { value: props.data.divre_code },
    divre_name: { value: props.data.divre_name }
});

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    data.divre_code = loc.divre_kode;
    data.divre_name = loc.divre_name;
    data.witel_code = loc.witel_kode;
    data.witel_name = loc.witel_name;
};

const listUserStore = useListUserStore();
const viewStore = useViewStore();

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = () => {
    const isValid = v$.value.$validate();
    const isLocationValid = props.isCurrUser || inputLocation.value.validate();
    if(!isValid || !isLocationValid)
        return;
    
    isLoading.value = true;
    const body = {
        nama: data.nama,
        organisasi: data.organisasi,
        role: data.role,
        no_hp: data.no_hp,
        email: data.email,
        telegram_id: data.telegram_id,
        telegram_username: data.telegram_username,
        witel_code: data.witel_code,
        witel_name: data.witel_name,
        divre_code: data.divre_code,
        divre_name: data.divre_name
    };

    listUserStore.update(data.id, body, response => {
        isLoading.value = null;
        hasSubmitted.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Update User", "Berhasil menyimpan perubahan.", true);
        listUserStore.fetchList(true);
    });
};
</script>
<template>
    <Dialog header="Edit Data User" v-model:visible="showDialog" modal maximizable draggable
        class="dialog-basic" @afterHide="$emit('die')">
        <form @submit.prevent="onSubmit" class="p-4">
            <div class="mb-4">
                <label for="inputNama">Nama<span class="text-danger"> *</span></label>
                <input type="text" v-model="v$.nama.$model" id="inputNama" :class="{ 'is-invalid': hasSubmitted && v$.nama.$invalid }" class="form-control" autofocus />
            </div>
            <div v-if="!isCurrUser" class="row">
                <div class="col-auto col-md mb-4">
                    <label>Role<span class="text-danger"> *</span></label>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="v$.role.$model" value="admin" id="radioRoleAdmin" class="radio_animated" />
                        <label for="radioRoleAdmin">Admin</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="v$.role.$model" value="viewer" id="radioRoleViewer" class="radio_animated" />
                        <label for="radioRoleViewer">Viewer</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="v$.role.$model" value="teknisi" id="radioRoleTeknisi" class="radio_animated" />
                        <label for="radioRoleTeknisi">Teknisi</label>
                    </div>
                </div>
                <div class="col-auto col-md mb-4">
                    <label>Organisasi<span class="text-danger"> *</span></label>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="v$.organisasi.$model" value="nasional" :disabled="userLevel != 'nasional'" id="radioOrgNasional" class="radio_animated" />
                        <label for="radioOrgNasional">Nasional</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="v$.organisasi.$model" value="divre" :disabled="userLevel == 'witel'" id="radioOrgDivre" class="radio_animated" />
                        <label for="radioOrgDivre">Divre</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="v$.organisasi.$model" value="witel" id="radioOrgWitel" class="radio_animated" />
                        <label for="radioOrgWitel">Witel</label>
                    </div>
                </div>
            </div>
            <InputGroupLocation v-if="!isCurrUser" ref="inputLocation" useLevel :level="data.organisasi" :divreValue="data.divre_code" :witelValue="data.witel_code" @change="onLocationChange" />
            <div class="mb-4">
                <label for="inputEmail">Email<span class="text-danger"> *</span></label>
                <input type="email" v-model="v$.email.$model" id="inputEmail" :class="{ 'is-invalid': hasSubmitted && v$.email.$invalid }" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputNoHp">Nomor Handphone<span class="text-danger"> *</span></label>
                <input type="tel" v-model="v$.no_hp.$model" id="inputNoHp" :class="{ 'is-invalid': hasSubmitted && v$.no_hp.$invalid }" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgId">Telegram Id</label>
                <input type="text" v-model="v$.telegram_id.$model" id="inputTelgId" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgUn">Telegram Username</label>
                <input type="text" v-model="v$.telegram_username.$model" id="inputTelgUn" class="form-control" />
            </div>
            <div class="d-flex justify-content-end mt-5">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </Dialog>
</template>