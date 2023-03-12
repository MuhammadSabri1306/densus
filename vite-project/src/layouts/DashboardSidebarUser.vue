<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useUserStore } from "@stores/user";

const emit = defineEmits(["updateUser", "updatePassword", "logout"])

const userStore = useUserStore();
const userName = computed(() => userStore.name);
const userLocation = computed(() => userStore.location);

const showSetting = ref(false);
const onBodyClick = () => showSetting.value = false;

onMounted(() => {
    document.body.addEventListener("click", onBodyClick);
});

onUnmounted(() => {
    document.body.removeEventListener("click", onBodyClick);
});

const userData = computed(() => userStore.userData);
const isUserDataLoading = ref(false);
const showUpdateUserForm = () => {
    isUserDataLoading.value = true;
    userStore.fetchUserData(false, () => {
        isUserDataLoading.value = false;
        emit("updateUser", userData.value);
    });
};
</script>
<template>
    <div class="sidebar-user text-center">
        <a class="setting-primary" role="button" title="setting" @click.stop="showSetting = !showSetting">
            <vue-feather type="settings" />
        </a>
        <ul :class="{ 'show': showSetting }" class="setting-wrapper">
            <li><p class="f-w-700 m-2">{{ userName }}</p></li>
            <li>
                <a role="button" title="update profil" @click="showUpdateUserForm">
                    <VueFeather v-if="isUserDataLoading" type="loader" animation="spin" size="1.5em" />
                    <VueFeather v-else type="user" size="1.5em" />
                    <span class="f-w-700 ms-2">Update Profil</span>
                </a>
            </li>
            <li>
                <a role="button" title="ganti password" @click="$emit('updatePassword')">
                    <VueFeather type="key" size="1.5em" />
                    <span class="f-w-700 ms-2">Ganti Password</span>
                </a>
            </li>
            <li>
                <a role="button" @click.stop="$emit('logout')" title="log out">
                    <VueFeather type="lock" size="1.5em" />
                    <span class="f-w-700 ms-2">Log Out</span>
                </a>
            </li>
        </ul>
        <img class="img-90 rounded-circle mx-auto" src="/assets/img/user1.png" alt="">
        <div class="badge-bottom">
            <span class="badge badge-primary">New</span>
        </div>
        <a href="#">
            <h6 class="mt-3 f-14 f-w-600">{{ userName }}</h6>
        </a>
        <p class="mb-0 font-roboto text-uppercase">{{ userLocation }}</p>
    </div>
</template>
<style scoped>

.setting-wrapper {
    position: absolute;
    top: 0;
    left: calc(100% + 10px);
    width: 14rem;
    z-index: 8;
    display: flex;
    flex-direction: column;
    background-color: #fff;
    border: 1px solid #f5f7fb;
    -webkit-box-shadow: 0 0 12px 3px rgb(25 124 207 / 5%);
    box-shadow: 0 0 12px 3px rgb(25 124 207 / 5%);
    opacity: 0;
    -webkit-transform: translateY(30px);
    transform: translateY(30px);
    visibility: hidden;
    -webkit-transition: all linear 0.3s;
    transition: all linear 0.3s;
}

.setting-wrapper.show {
    opacity: 1;
    -webkit-transform: translateY(0px);
    transform: translateY(0px);
    visibility: visible;
}

.setting-wrapper li {
    display: block;
}

.setting-wrapper li + li {
    border-top: 1px solid #e6edef;
}

.setting-wrapper a {
    display: flex;
    align-items: center;
    padding: 15px 30px;
}

.setting-wrapper a:hover,
.setting-wrapper a:active {
    color: #24695c;
}

</style>