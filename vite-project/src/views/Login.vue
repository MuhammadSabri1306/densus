<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { useDataForm } from "@/helpers/data-form";
import { required } from "@vuelidate/validators";
import InputSwitch from "primevue/inputswitch";
import Checkbox from "primevue/checkbox";

const { data, v$ } = useDataForm({
    is_ldap: { value: false, required },
    username: { required },
    password: { required }
});

const userStore = useUserStore();
const viewStore = useViewStore();
const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        is_ldap: data.is_ldap,
        username: data.username,
        password: data.password
    };

    isLoading.value = true;
    userStore.login(body, response => {
        isLoading.value = false;
        hasSubmitted.value = false;

        if(!response.success && response.status === 400)
            viewStore.showToast("Login User", "Harap masukkan sername dan password.", false);
        else if(!response.success)
            viewStore.showToast("Koneksi Error", "Terjadi masalah saat menghubungi server.", false);
        else
            setTimeout(() => router.push(route.query.redirect || '/'), 500);
    });
};

const showPass = ref(false);
const typePass = computed(() => showPass.value ? "text" : "password");
</script>
<template>
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-7" id="loginHero">
                    <img class="d-none" src="/assets/img/2.png" alt="login page">
                </div>
                <div class="col-xl-5 p-0">
                    <div class="login-card flex-column">
                        <form @submit.prevent="onSubmit" class="theme-form login-form">
                            <h4>Login ke DENSUS Dashboard</h4>
                            <h6>Welcome ! Log in to your account.</h6>
                            <div class="row align-items-center my-4">
                                <div class="col-auto mb-2">
                                    <label for="switchIsLdap">Gunakan LDAP</label>
                                </div>
                                <div class="col-auto mb-2">
                                    <InputSwitch v-model="data.is_ldap" inputId="switchIsLdap" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ data.is_ldap ? "NIK" : "Username" }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><VueFeather type="mail" size="1em" /></span>
                                    <input v-model="data.username" :class="{ 'is-invalid': hasSubmitted && v$.username.$invalid }" class="form-control" type="text" placeholder="950022">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><VueFeather type="lock" size="1em" /></span>
                                    <input v-model="data.password" :class="{ 'is-invalid': hasSubmitted && v$.password.$invalid }" class="form-control" :type="typePass" name="password" placeholder="*********">
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-auto mt-4">
                                    <div class="d-flex align-items-center">
                                        <Checkbox v-model="showPass" inputId="cbShowPass" :binary="true" />
                                        <label for="cbShowPass" class="ms-2 mb-0">Tampilkan Password</label>
                                    </div>
                                </div>
                                <div class="col-auto ms-auto mt-4">
                                    <button :class="{ 'btn-loading': isLoading }" class="btn btn-primary btn-block" type="submit">Sign in</button>
                                </div>
                            </div>
                        </form>
                        <div class="row align-items-center justify-content-center mt-5">
                            <div class="col-auto logo-support">
                                <img src="/assets/img/WeCare-logo.webp" class="img-fluid" alt="logo we care" />
                            </div>
                            <div class="col-auto logo-support">
                                <img src="/assets/img/Gepee-logo.webp" class="img-fluid" alt="logo gepee" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<style scoped>

#loginHero {
    background-image: url('/assets/img/2.png');
    background-size: cover;
    background-position: center center;
    display: block;
}

.logo-support:first-child img {
    width: 4.5rem;
}

.logo-support:nth-child(2) img {
    width: 10rem;
}

</style>