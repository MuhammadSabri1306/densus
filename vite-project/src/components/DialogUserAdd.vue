<script setup>
import { ref, computed, watch } from "vue";
import { useUserStore } from "@stores/user";
import { useLocationStore } from "@stores/location";
import { useListUserStore } from "@stores/list-user";
import { useViewStore } from "@stores/view";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import Dialog from "primevue/dialog";
import InputSwitch from "primevue/inputswitch";
import InputGroupLocation from "@components/InputGroupLocation.vue";

const emit = defineEmits(["saved", "die"]);

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);
const showDialog = ref(true);

const { data, v$ } = useDataForm({
    nama: { required },
    organisasi: { value: "witel", required },
    role: { value: "teknisi", required },
    no_hp: { required },
    email: { required },
    is_ldap: { value: false, required },
    telegram_id: {},
    telegram_username: {},
    witel_code: {},
    witel_name: {},
    divre_code: {},
    divre_name: {},
    username: { required },
    password1: {},
    password2: {}
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
const isPassEqual = computed(() => data.password1 === data.password2);

const optValidation = () => {
    if(!inputLocation.value.validate())
        return false;

    if(!data.is_ldap) {
        if(!data.password1)
            return false;
        if(!data.password2)
            return false;
        if(!isPassEqual.value)
            return false;
    }

    return true;
};

const onSubmit = async () => {
    hasSubmitted.value = true;
    let isValid = await v$.value.$validate();
    isValid = isValid && optValidation();

    if(!isValid)
        return;
    
    const body = {
        nama: data.nama,
        organisasi: data.organisasi,
        role: data.role,
        no_hp: data.no_hp,
        email: data.email,
        is_ldap: data.is_ldap,
        telegram_id: data.telegram_id,
        telegram_username: data.telegram_username,
        witel_code: data.witel_code,
        witel_name: data.witel_name,
        divre_code: data.divre_code,
        divre_name: data.divre_name,
        username: data.username
    };

    if(!data.is_ldap)
        body.password = data.password1;
    
    isLoading.value = true;
    listUserStore.create(body, response => {
        isLoading.value = false;
        hasSubmitted.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Buat User", "Berhasil menyimpan data user.", true);
        listUserStore.fetchList(true);
        showDialog.value = false;
    });
};
</script>
<template>
    <Dialog header="Buat User Baru" v-model:visible="showDialog" modal maximizable draggable @afterHide="$emit('die')">
        <form @submit.prevent="onSubmit" class="p-4">
            <div class="mb-4">
                <label for="inputNama">Nama <span class="text-danger">*</span></label>
                <input type="text" v-model="data.nama" id="inputNama" :class="{ 'is-invalid': hasSubmitted && v$.nama.$invalid }" class="form-control" autofocus />
            </div>
            <div class="row">
                <div class="col-auto col-md mb-4">
                    <label>Role <span class="text-danger">*</span></label>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="data.role" value="admin" id="radioRoleAdmin" class="radio_animated" />
                        <label for="radioRoleAdmin">Admin</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="data.role" value="viewer" id="radioRoleViewer" class="radio_animated" />
                        <label for="radioRoleViewer">Viewer</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="data.role" value="teknisi" id="radioRoleTeknisi" class="radio_animated" />
                        <label for="radioRoleTeknisi">Teknisi</label>
                    </div>
                </div>
                <div class="col-auto col-md mb-4">
                    <label>Organisasi <span class="text-danger">*</span></label>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="data.organisasi" value="nasional" :disabled="userLevel != 'nasional'" id="radioOrgNasional" class="radio_animated" />
                        <label for="radioOrgNasional">Nasional</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="data.organisasi" value="divre" :disabled="userLevel == 'witel'" id="radioOrgDivre" class="radio_animated" />
                        <label for="radioOrgDivre">Divre</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="data.organisasi" value="witel" id="radioOrgWitel" class="radio_animated" />
                        <label for="radioOrgWitel">Witel</label>
                    </div>
                </div>
            </div>
            
            <InputGroupLocation ref="inputLocation" useLevel :level="data.organisasi" :divreValue="data.divre_code" :witelValue="data.witel_code" @change="onLocationChange" />

            <div class="mb-4">
                <label for="inputEmail">Email <span class="text-danger">*</span></label>
                <input type="email" v-model="data.email" :class="{ 'is-invalid': hasSubmitted && v$.email.$invalid }" id="inputEmail" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputNoHp">Nomor Handphone <span class="text-danger">*</span></label>
                <input type="tel" v-model="data.no_hp" id="inputNoHp" :class="{ 'is-invalid': hasSubmitted && v$.no_hp.$invalid }" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgId">Telegram Id</label>
                <input type="text" v-model="data.telegram_id" id="inputTelgId" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgUn">Telegram Username</label>
                <input type="text" v-model="data.telegram_username" id="inputTelgUn" class="form-control" />
            </div>
            <div class="row">
                <div class="col-auto mb-4 mx-4">
                    <label for="switchLdap" class="d-block">LDAP</label>
                    <InputSwitch v-model="data.is_ldap" inputId="switchLdap" />
                </div>
                <div class="col-12 col-md mb-4">
                    <label for="inputUsername">Username <span class="text-danger">*</span></label>
                    <input type="text" v-model="data.username" :class="{ 'is-invalid': hasSubmitted && !data.username }" id="inputUsername" class="form-control" />
                </div>
            </div>
            <div v-if="!data.is_ldap">
                <div class="mb-4">
                    <label for="inputPass1">Masukkan Password <span class="text-danger">*</span></label>
                    <input type="password" v-model="data.password1" :class="{ 'is-invalid': hasSubmitted && !isPassEqual }" id="inputPass1" class="form-control" />
                </div>
                <div class="mb-4">
                    <label for="inputPass2">Masukkan ulang Password <span class="text-danger">*</span></label>
                    <input type="password" v-model="data.password2" :class="{ 'is-invalid': hasSubmitted && !isPassEqual }" id="inputPass2" class="form-control" />
                </div>
            </div>
            <div class="d-flex justify-content-end mt-5">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </Dialog>
</template>