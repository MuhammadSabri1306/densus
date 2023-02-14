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
import ListboxRegional from "@components/ListboxRegional.vue";
import ListboxWitel from "@components/ListboxWitel.vue";

const emit = defineEmits(["saved", "die"]);

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);

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

const locationStore = useLocationStore();
const listboxRegional = ref(null);
const listboxWitel = ref(null);
const showLbRegional = ref(true);
const showLbWitel = ref(true);

watch(() => data.organisasi, org => {
    if(org == "witel") {
        showLbRegional.value = true;
        showLbWitel.value = true;
    } else if(org == "divre") {
        showLbRegional.value = true;
        showLbWitel.value = false;
    } else {
        showLbRegional.value = false;
        showLbWitel.value = false;
    }
});

const onDivreChange = ({ code, name }) => {
    data.divre_code = code;
    data.divre_name = name;
    console.log(code);
    locationStore.fetchWitel(code);
};

const onWitelChange = ({ code, name }) => {
    data.witel_code = code;
    data.witel_name = name;
};

const listUserStore = useListUserStore();
const viewStore = useViewStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const isPassEqual = computed(() => data.password1 === data.password2);

const optValidation = () => {
    if(data.organisasi != "nasional") {
        if(listboxRegional.value)
            listboxRegional.value.validate();
        if(listboxWitel.value)
            listboxWitel.value.validate();
        if(!data.divre_code || !data.witel_code)
            return false;
    }

    if(!data.is_ldap) {
        if(!data.password1)
            return false;
        if(!data.password2)
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
        username: data.username,
        password: data.password1
    };
    
    isLoading.value = true;
    listUserStore.create(body, response => {
        isLoading.value = null;
        hasSubmitted.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Buat User", "Berhasil menyimpan data user.", true);
        listUserStore.fetchList(true);
        emit("saved");
    });
};

const resetComp = () => {
    isLoading.value = false;
    hasSubmitted.value = false;

    data.nama = null;
    data.organisasi = "witel";
    data.role = "teknisi";
    data.no_hp = null;
    data.email = null;
    data.is_ldap = false;
    data.telegram_id = null;
    data.telegram_username = null;
    data.witel_code = null;
    data.witel_name = null;
    data.divre_code = null;
    data.divre_name = null;
    data.username = null;
    data.password1 = null;
    data.password2 = null;

    emit("die");
};

</script>
<template>
    <Dialog header="Buat User Baru" modal maximizable draggable @hide="resetComp">
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
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="inputRegional" class="col-form-label">Regional</label>
                        <ListboxRegional v-if="showLbRegional" ref="listboxRegional" isRequired @change="onDivreChange" />
                        <input type="text" v-else id="inputRegional" placeholder="Pilih Regional" disabled class="form-control" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="inputWitel" class="col-form-label">Regional</label>
                        <ListboxWitel v-if="showLbWitel && data.divre_code" ref="listboxWitel" :divre="data.divre_code" isRequired @change="onWitelChange" />
                        <input type="text" v-else :divre="data.divre_code" id="inputWitel" placeholder="Pilih Witel" disabled class="form-control" />
                    </div>
                </div>
            </div>

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