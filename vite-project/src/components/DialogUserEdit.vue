<script setup>
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import InputSwitch from "primevue/inputswitch";

const emit = defineEmits(["saved", "die"]);
const props = defineProps({
    data: Object
});

const currUser = ref(props.data);
watch(() => props.data, data => currUser.value = data);

const isLoading = ref(false);
const hasSubmitted = ref(false);
const onSubmit = () => {
    isLoading.value = true;
};

const resetComp = () => {
    isLoading.value = false;
    hasSubmitted.value = false;
    currUser.value = {};
    emit("die");
};
</script>
<template>
    <Dialog header="Edit Data User" modal maximizable draggable @hide="resetComp">
        <form @submit.prevent="onSubmit" class="p-4">
            <div class="mb-4">
                <label for="inputNama">Nama</label>
                <input type="text" v-model="currUser.nama" id="inputNama" class="form-control" autofocus />
            </div>
            <div class="mb-4">
                <label for="inputUsername">Username</label>
                <input type="text" v-model="currUser.username" id="inputUsername" class="form-control" />
            </div>
            <div class="mb-4">
                <label>Role</label>
                <div class="row">
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.role" value="admin" id="radioRoleAdmin" class="radio_animated" />
                        <label for="radioRoleAdmin">Admin</label>
                    </div>
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.role" value="viewer" id="radioRoleViewer" class="radio_animated" />
                        <label for="radioRoleViewer">Viewer</label>
                    </div>
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.role" value="teknisi" id="radioRoleTeknisi" class="radio_animated" />
                        <label for="radioRoleTeknisi">Teknisi</label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label>Organisasi</label>
                <div class="row">
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="nasional" id="radioOrgNasional" class="radio_animated" />
                        <label for="radioOrgNasional">Nasional</label>
                    </div>
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="divre" id="radioOrgDivre" class="radio_animated" />
                        <label for="radioOrgDivre">Divre</label>
                    </div>
                    <div class="col-auto mx-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="witel" id="radioOrgWitel" class="radio_animated" />
                        <label for="radioOrgWitel">Witel</label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="inputDivre">Divre</label>
                <input type="text" v-model="currUser.divre" :disabled="currUser.organisasi != 'divre'" id="inputDivre" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputWitel">Witel</label>
                <input type="text" v-model="currUser.witel" :disabled="currUser.organisasi != 'witel'" id="inputWitel" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputEmail">Email</label>
                <input type="email" v-model="currUser.email" id="inputEmail" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputNoHp">Nomor Handphone</label>
                <input type="tel" v-model="currUser.no_hp" id="inputNoHp" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgId">Telegram Id</label>
                <input type="text" v-model="currUser.telegram_id" id="inputTelgId" class="form-control" />
            </div>
            <div class="mb-4">
                <label for="inputTelgUn">Telegram Username</label>
                <input type="text" v-model="currUser.telegram_username" id="inputTelgUn" class="form-control" />
            </div>
            <div class="row mb-5">
                <div class="col-auto mx-4">
                    <label for="switchLdap" class="d-block">LDAP</label>
                    <InputSwitch v-model="currUser.is_ldap" inputId="switchLdap" />
                </div>
                <div class="col-auto mx-4">
                    <label for="switchIsActive" class="d-block">Aktif</label>
                    <InputSwitch v-model="currUser.is_active" inputId="switchIsActive" />
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </Dialog>
</template>