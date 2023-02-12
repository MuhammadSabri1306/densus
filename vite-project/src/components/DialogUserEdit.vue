<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useListUserStore } from "@stores/list-user";
import Dialog from "primevue/dialog";
import InputSwitch from "primevue/inputswitch";
import ListboxRegWitel from "@components/ListboxRegWitel.vue";

const emit = defineEmits(["saved", "die"]);
const props = defineProps({
    data: Object
});

const currUser = ref(props.data);
watch(() => props.data, data => currUser.value = data);

const onDivreChange = val => {
    currUser.value.divre_code = val.divreCode;
    currUser.value.divre_name = val.divreName;
};

const onWitelChange = val => {
    currUser.value.witel_code = val.witelCode;
    currUser.value.witel_name = val.witelName;
};

const listUserStore = useListUserStore();
const viewStore = useViewStore();
const router = useRouter();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = () => {
    isLoading.value = true;
    hasSubmitted.value = true;

    const body = {
        nama: currUser.value.nama,
        organisasi: currUser.value.organisasi,
        role: currUser.value.admin,
        no_hp: currUser.value.no_hp,
        email: currUser.value.email,
        is_ldap: currUser.value.is_ldap,
        telegram_id: currUser.value.telegram_id,
        telegram_username: currUser.value.telegram_username,
        witel_code: currUser.value.witel_code,
        witel_name: currUser.value.witel_name,
        divre_code: currUser.value.divre_code,
        divre_name: currUser.value.divre_name,
        username: currUser.value.username,
    };

    listUserStore.update(currUser.value.id, body, response => {
        isLoading.value = null;
        hasSubmitted.value = false;
        if(!response.success)
            return;

        viewStore.showToast("Update User", "Berhasil menyimpan perubahan.", true);
        listUserStore.fetchList(true);
    });
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
            <ListboxRegWitel ref="listboxRegWitel" fieldRequired :defaultDivre="currUser.divre_code" :defaultWitel="currUser.witel_code" @divreChange="onDivreChange" @witelChange="onWitelChange" class="mb-4" />
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
            <div class="row">
                <div class="col-auto mx-4 mb-4">
                    <label for="switchLdap" class="d-block">LDAP</label>
                    <InputSwitch v-model="currUser.is_ldap" inputId="switchLdap" />
                </div>
                <div class="col-auto mb-4">
                    <label for="inputUsername">Username</label>
                    <input type="text" v-model="currUser.username" id="inputUsername" class="form-control" />
                </div>
            </div>
            <div class="d-flex justify-content-end mt-5">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </Dialog>
</template>