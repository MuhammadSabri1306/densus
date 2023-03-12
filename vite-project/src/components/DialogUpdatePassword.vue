<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import Dialog from "primevue/dialog";

const emit = defineEmits(["close"]);
const showDialog = ref(true);

const onHide = () => {
    showDialog.value = false;
    emit("close");
};

const { data, v$ } = useDataForm({
    oldPass: { required },
    newPass1: { required },
    newPass2: { required }
});

const isLoading = ref(false);
const hasSubmitted = ref(false);

const isNewPassMatch = computed(() => {
    if(!hasSubmitted.value)
        return true;
    return data.newPass1 === data.newPass2;
});

const userStore = useUserStore();
const viewStore = useViewStore();

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid || !isNewPassMatch.value)
        return false;
    
    const body = {
        old_password: data.oldPass,
        new_password: data.newPass1
    };
    isLoading.value = true;
    userStore.updatePassword(body, response => {
        isLoading.value = false;
        if(response.success) {
            viewStore.showToast("Update Password", "Berhasil menyimpan password baru.", true);
            showDialog.value = false;
        }
    });
};
</script>
<template>
    <Dialog header="Update Password" v-model:visible="showDialog" modal draggable @afterHide="onHide">
        <form @submit.prevent="onSubmit" class="p-4">
            <div class="mb-4">
                <label for="inputOldPass">Masukkan password lama <span class="text-danger">*</span></label>
                <input type="password" v-model="data.oldPass" id="inputOldPass" :class="{ 'is-invalid': hasSubmitted && v$.oldPass.$invalid }" class="form-control" autofocus />
            </div>
            <div class="mb-4">
                <label for="inputNewPass1">Masukkan password baru <span class="text-danger">*</span></label>
                <input type="password" v-model="data.newPass1" id="inputNewPass1" :class="{ 'is-invalid': hasSubmitted && v$.newPass1.$invalid }" class="form-control" />
            </div>
            <div class="mb-5">
                <label for="inputNewPass2">Masukkan ulang password baru <span class="text-danger">*</span></label>
                <input type="password" v-model="data.newPass2" id="inputNewPass2" :class="{ 'is-invalid': hasSubmitted && v$.newPass2.$invalid }" class="form-control" />
                <p v-if="!isNewPassMatch" class="mb-0 font-danger ms-4">Password baru tidak cocok</p>
            </div>
            <div class="d-flex justify-content-between align-items-end">
                <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-success btn-lg">Update Password</button>
                <button type="button" @click="showDialog = false" class="btn btn-secondary">Batalkan</button>
            </div>
        </form>
    </Dialog>
</template>