<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import Footer from "./Footer.vue";
import SidebarUser from "./DashboardSidebarUser.vue";
import SidebarMenu from "./DashboardSidebarMenu.vue";
import DialogUpdatePassword from "@components/DialogUpdatePassword.vue";
import DialogUserEdit from "@components/DialogUserEdit.vue";

import "bootstrap/dist/css/bootstrap.min.css";
import "@/assets/css/app.css";
import "@/assets/css/theme.css";
import "@/assets/css/responsive.css";
import "@/assets/css/pue-color.css";
import "@/assets/css/percentage-color.css";

const viewStore = useViewStore();
const hideSidebar = computed(() => viewStore.hideSidebar);

const router = useRouter();
router.beforeEach(() => {
    viewStore.resetSidebarVisibility();
});

const setupSidebarVisibility = () => viewStore.resetSidebarVisibility();
onMounted(() => window.addEventListener("resize", setupSidebarVisibility));
onUnmounted(() => window.addEventListener("resize", setupSidebarVisibility));

viewStore.resetSidebarVisibility();

const userStore = useUserStore();
const logout = () => {
    if(!confirm("Anda akan keluar dari DENSUS. Lanjutkan?"))
        return;
    userStore.logout();
    router.push("/login");
};

const showPassForm = ref(false);
const profileUpdateData = ref(null);
</script>
<template>
    <div class="page-wrapper compact-wrapper viho-theme" id="pageWrapper">
        <!-- Page Header Start-->
        <div :class="{ 'close_icon': hideSidebar }" class="page-main-header">
            <div class="main-header-right row m-0">
                <div class="main-header-left">
                    <div class="logo-wrapper">
                        <RouterLink to="/" title="Dashboard">
                            <img src="/assets/img/logo-densus.png" class="img-fluid brand-logo" alt="" />
                        </RouterLink>
                    </div>
                    <div class="dark-logo-wrapper">
                        <RouterLink to="/" title="Dashboard">
                            <img src="/assets/img/logo-densus.png" class="img-fluid brand-logo" alt="" />
                        </RouterLink>
                    </div>
                    <div @click="viewStore.toggleSidebarVisibility()" class="toggle-sidebar" tabindex="0" role="button" aria-pressed="false" title="Toggle Sidebar">
                        <vue-feather type="align-center" class="status_toggle middle" />
                    </div>
                </div>
                <div class="left-menu-header col">
                    <ul>
                        <li>
                            <form class="form-inline search-form">
                                <div class="search-bg">
                                    <vue-feather type="search" strokeWidth="3.8" style="width: 0.9rem;" />
                                    <input class="form-control-plaintext" placeholder="Search here.....">
                                </div>
                            </form>
                            <span class="d-sm-none mobile-search search-bg">
                                <vue-feather type="search" strokeWidth="3.8" style="width: 0.9rem;" />
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="nav-right col pull-right right-menu p-0">
                    <ul class="nav-menus">
                        <li>
                            <a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()">
                                <vue-feather type="maximize" />
                            </a>
                        </li>
                        <li class="onhover-dropdown">
                            <div class="notification-box">
                                <vue-feather type="bell" />
                                <span class="dot-animated"></span>
                            </div>
                            <ul class="notification-dropdown onhover-show-div">
                                <li>
                                    <p class="f-w-700 mb-0">You have 3 Notifications<span class="pull-right badge badge-primary badge-pill">4</span></p>
                                </li>
                                <li class="noti-primary">
                                    <div class="media">
                                        <span class="notification-bg bg-light-primary">
                                            <vue-feather type="activity" />
                                        </span>
                                        <div class="media-body">
                                            <p>Delivery processing </p><span>10 minutes ago</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="noti-secondary">
                                    <div class="media">
                                        <span class="notification-bg bg-light-secondary">
                                            <vue-feather type="check-circle" />
                                        </span>
                                        <div class="media-body">
                                            <p>Order Complete</p><span>1 hour ago</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="noti-success">
                                    <div class="media">
                                        <span class="notification-bg bg-light-success">
                                            <vue-feather type="file-text" />
                                        </span>
                                        <div class="media-body">
                                            <p>Tickets Generated</p><span>3 hour ago</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="noti-danger">
                                    <div class="media">
                                        <span class="notification-bg bg-light-danger">
                                            <vue-feather type="user-check" />
                                        </span>
                                        <div class="media-body">
                                            <p>Delivery Complete</p><span>6 hour ago</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="onhover-dropdown p-0">
                            <button @click="logout" class="btn btn-primary-light btn-icon"><vue-feather type="log-out" />Log out</button>
                        </li>
                    </ul>
                </div>
                <div class="d-lg-none mobile-toggle pull-right w-auto">
                    <vue-feather type="more-horizontal" />
                </div>
            </div>
        </div>
        <!-- Page Header Ends -->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <header :class="{ 'close_icon': !hideSidebar }" class="main-nav">
                <SidebarUser @updateUser="val => profileUpdateData = val" @updatePassword="showPassForm = true" @logout="logout" />
                <SidebarMenu />
            </header>
            <main class="page-body">
                <RouterView />
            </main>
            <Footer />
        </div>        
        <DialogUpdatePassword v-if="showPassForm" @close="showPassForm = false" />
        <DialogUserEdit v-if="profileUpdateData" isCurrUser :data="profileUpdateData" @die="profileUpdateData = null" />
    </div>
</template>
<style scoped>

.brand-logo {
    max-width: 5.2rem;
}

.page-wrapper.compact-wrapper .page-body-wrapper header.main-nav,
:deep(.page-wrapper).compact-wrapper .page-body-wrapper header.main-nav {
    z-index: 88;
}

.card-h-full,
:deep(.card-h-full) {
    height: calc(100% - 30px);
    margin-bottom: 30px;
}

</style>