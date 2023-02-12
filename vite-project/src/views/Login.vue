<script setup>
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";

const { data, v$ } = useDataForm({
    is_ldap: { value: 0 },
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
</script>
<template>
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-7" id="loginHero">
                    <img class="d-none" src="/assets/img/2.png" alt="login page">
                </div>
                <div class="col-xl-5 p-0">
                    <div class="login-card">
                        <form @submit.prevent="onSubmit" class="theme-form login-form">
                            <h4>Login ke DENSUS Dashboard</h4>
                            <h6>Welcome ! Log in to your account.</h6>
                            <div class="form-group">
                                <label>NIK/Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><VueFeather type="mail" size="1em" /></span>
                                    <input v-model="data.username" :class="{ 'is_invalid': hasSubmitted && v$.username.$invalid }" class="form-control" type="text" placeholder="950022">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Password LDAP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><VueFeather type="lock" size="1em" /></span>
                                    <input v-model="data.password" :class="{ 'is_invalid': hasSubmitted && v$.password.$invalid }" class="form-control" type="password" name="password" placeholder="*********">
                                </div>
                            </div>
                            <div class="form-group">
                                <button :class="{ 'btn-loading': isLoading }" class="btn btn-primary btn-block" type="submit">Sign in</button>
                            </div>
                        </form>
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

</style>