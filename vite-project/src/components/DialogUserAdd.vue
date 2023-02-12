<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useListUserStore } from "@stores/list-user";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import Dialog from "primevue/dialog";
import InputSwitch from "primevue/inputswitch";
import ListboxRegWitel from "@components/ListboxRegWitel.vue";

const emit = defineEmits(["saved", "die"]);
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
    username: {},
    password1: {},
    password2: {}
});

const listboxRegWitel = ref(null);

const onDivreChange = val => {
    data.divre_code = val.divreCode;
    data.divre_name = val.divreName;
};

const onWitelChange = val => {
    data.witel_code = val.witelCode;
    data.witel_name = val.witelName;
};

const listUserStore = useListUserStore();
const viewStore = useViewStore();
const router = useRouter();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const isPassEqual = computed(() => data.password1 === data.password2);

const optValidation = () => {
    if(data.organisasi == "witel") {
        listboxRegWitel.value.validate();
        if(!data.divre_code || !data.witel_code)
            return false;
    }

    if(!data.is_ldap) {
        if(!data.username)
            return false;
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
    console.log(data)
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
                        <input type="radio" v-model="data.organisasi" value="nasional" id="radioOrgNasional" class="radio_animated" />
                        <label for="radioOrgNasional">Nasional</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="data.organisasi" value="divre" id="radioOrgDivre" class="radio_animated" />
                        <label for="radioOrgDivre">Divre</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="data.organisasi" value="witel" id="radioOrgWitel" class="radio_animated" />
                        <label for="radioOrgWitel">Witel</label>
                    </div>
                </div>
            </div>
            <ListboxRegWitel ref="listboxRegWitel" fieldRequired :defaultDivre="data.divre_code" :defaultWitel="data.witel_code" @divreChange="onDivreChange" @witelChange="onWitelChange" class="mb-4" />
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
                <div v-if="!data.is_ldap" class="col-12 col-md mb-4">
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