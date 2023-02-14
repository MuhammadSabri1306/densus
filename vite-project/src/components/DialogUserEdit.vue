<script setup>
import { ref, computed, watch } from "vue";
import { useUserStore } from "@stores/user";
import { useLocationStore } from "@stores/location";
import { useViewStore } from "@stores/view";
import { useListUserStore } from "@stores/list-user";
import Dialog from "primevue/dialog";
import ListboxRegional from "@components/ListboxRegional.vue";
import ListboxWitel from "@components/ListboxWitel.vue";

const emit = defineEmits(["saved", "die"]);
const props = defineProps({
    data: Object
});

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);

const currUser = ref(props.data);
watch(() => props.data, data => {
    currUser.value = data;
    if(data.divre_code)
        locationStore.fetchWitel(data.divre_code);
});

const locationStore = useLocationStore();
const listboxRegional = ref(null);
const listboxWitel = ref(null);
const showLbRegional = ref(true);
const showLbWitel = ref(true);

watch(() => currUser.value.organisasi, org => {
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
    currUser.value.divre_code = code;
    currUser.value.divre_name = name;
    locationStore.fetchWitel(code);
};

const onWitelChange = ({ code, name }) => {
    currUser.value.witel_code = code;
    currUser.value.witel_name = name;
};

const listUserStore = useListUserStore();
const viewStore = useViewStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const optValidation = () => {
    if(currUser.value.organisasi != "nasional") {
        if(listboxRegional.value)
            listboxRegional.value.validate();
        if(listboxWitel.value)
            listboxWitel.value.validate();
        if(!currUser.value.divre_code || !currUser.value.witel_code)
            return false;
    }

    return true;
};

const onSubmit = async () => {
    let isValid = await v$.value.$validate();
    console.log(currUser.value)
    isValid = isValid && optValidation();
    if(!isValid)
        return;
    
    isLoading.value = true;
    const body = {
        nama: currUser.value.nama,
        organisasi: currUser.value.organisasi,
        role: currUser.value.role,
        no_hp: currUser.value.no_hp,
        email: currUser.value.email,
        telegram_id: currUser.value.telegram_id,
        telegram_username: currUser.value.telegram_username,
        witel_code: currUser.value.witel_code,
        witel_name: currUser.value.witel_name,
        divre_code: currUser.value.divre_code,
        divre_name: currUser.value.divre_name
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
            <div class="row">
                <div class="col-auto col-md mb-4">
                    <label>Role <span class="text-danger">*</span></label>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="currUser.role" value="admin" id="radioRoleAdmin" class="radio_animated" />
                        <label for="radioRoleAdmin">Admin</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="currUser.role" value="viewer" id="radioRoleViewer" class="radio_animated" />
                        <label for="radioRoleViewer">Viewer</label>
                    </div>
                    <div class="ms-4 mb-2">
                        <input type="radio" v-model="currUser.role" value="teknisi" id="radioRoleTeknisi" class="radio_animated" />
                        <label for="radioRoleTeknisi">Teknisi</label>
                    </div>
                </div>
                <div class="col-auto col-md mb-4">
                    <label>Organisasi <span class="text-danger">*</span></label>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="nasional" :disabled="userLevel != 'nasional'" id="radioOrgNasional" class="radio_animated" />
                        <label for="radioOrgNasional">Nasional</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="divre" :disabled="userLevel == 'witel'" id="radioOrgDivre" class="radio_animated" />
                        <label for="radioOrgDivre">Divre</label>
                    </div>
                    <div class="ms-4 my-2">
                        <input type="radio" v-model="currUser.organisasi" value="witel" id="radioOrgWitel" class="radio_animated" />
                        <label for="radioOrgWitel">Witel</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="inputRegional" class="col-form-label">Regional</label>
                        <ListboxRegional v-if="showLbRegional" ref="listboxRegional" :defaultValue="currUser.divre_code" isRequired @change="onDivreChange" />
                        <input type="text" v-else id="inputRegional" placeholder="Pilih Regional" disabled class="form-control" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <label for="inputWitel" class="col-form-label">Regional</label>
                        <ListboxWitel v-if="showLbWitel && currUser.divre_code" ref="listboxWitel" :divre="currUser.divre_code" isRequired @change="onWitelChange" />
                        <input type="text" v-else :divre="currUser.divre_code" :defaultValue="currUser.witel_code" id="inputWitel" placeholder="Pilih Witel" disabled class="form-control" />
                    </div>
                </div>
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
            <div class="d-flex justify-content-end mt-5">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </Dialog>
</template>