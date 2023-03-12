<script setup>
import { computed } from "vue";
import { useUserStore } from "@stores/user";

const userStore = useUserStore();
const name = computed(() => userStore.name);
const location = computed(() => userStore.location);
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="home" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Dashboard</span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec px-lg-5">
            <div class="row g-lg-5">
                <div class="col-md-6">
                    <div class="card d-lg-none card-welcome">
                        <div class="card-header pb-0">
                            <p><span>Selamat Datang</span></p>
                        </div>
                        <div class="card-body pt-0">
                            <h2 class="mb-0"><b class="text-capitalize font-primary">{{ name }}</b></h2>
                            <h6>dari <b class="text-capitalize">{{ location }}</b></h6>
                        </div>
                    </div>
                    <div class="card animated-card" style="z-index:2;">
                        <div class="img-wrapper">
                            <img src="/assets/img/dashboard.jpg" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex flex-column">
                    <div class="d-none d-md-flex row align-items-center justify-content-end mb-4">
                        <div class="col-auto logo-support">
                            <img src="/assets/img/WeCare-logo.webp" class="img-fluid" alt="logo we care" />
                        </div>
                        <div class="col-auto logo-support">
                            <img src="/assets/img/Gepee-logo.webp" class="img-fluid" alt="logo gepee" />
                        </div>
                    </div>
                    <div class="bg-home">
                        <div class="card d-none d-lg-block mb-5 card-welcome">
                            <div class="card-header pb-0">
                                <p><span>Selamat Datang</span></p>
                            </div>
                            <div class="card-body pt-0">
                                <h2 class="mb-0"><b class="text-capitalize font-primary">{{ name }}</b></h2>
                                <h6>dari <b class="text-capitalize">{{ location }}</b></h6>
                            </div>
                        </div>
                        <div class="row g-lg-5">
                            <div class="col">
                                <div class="card income-card card-primary" id="card-monitoring">                                 
                                    <div class="card-body text-center">                                  
                                        <div class="round-box">
                                            <VueFeather type="airplay" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
                                        </div>
                                        <h5>Monitoring RTU</h5>
                                        <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ab, incidunt?</p>
                                        <RouterLink to="/monitoring" class="btn-arrow arrow-primary">
                                            <i><VueFeather type="arrow-up-right" strokeWidth="4" size="0.8rem" class="toprightarrow-primary me-2 ms-1 mt-1" /></i>Buka
                                        </RouterLink>
                                        <div class="parrten">
                                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 448.057 448.057" style="enable-background:new 0 0 448.057 448.057;" xml:space="preserve">
                                                <g><g><path d="M404.562,7.468c-0.021-0.017-0.041-0.034-0.062-0.051c-13.577-11.314-33.755-9.479-45.069,4.099 c-0.017,0.02-0.034,0.041-0.051,0.062l-135.36,162.56L88.66,11.577C77.35-2.031,57.149-3.894,43.54,7.417                                            c-13.608,11.311-15.471,31.512-4.16,45.12l129.6,155.52h-40.96c-17.673,0-32,14.327-32,32s14.327,32,32,32h64v144                                            c0,17.673,14.327,32,32,32c17.673,0,32-14.327,32-32v-180.48l152.64-183.04C419.974,38.96,418.139,18.782,404.562,7.468z"></path></g></g><g><g><path d="M320.02,208.057h-16c-17.673,0-32,14.327-32,32s14.327,32,32,32h16c17.673,0,32-14.327,32-32 S337.694,208.057,320.02,208.057z"></path></g></g>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card income-card card-primary" id="card-gepee">                                 
                                    <div class="card-body text-center">                                  
                                        <div class="round-box">
                                            <VueFeather type="zap" style="enable-background:new 0 0 448.057 448.057;color:#24695c;" xml:space="preserve" />
                                        </div>
                                        <h5>Gepee Activity</h5>
                                        <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Temporibus illo distinctio beatae officia iusto!</p>
                                        <RouterLink to="/gepee" class="btn-arrow arrow-primary">
                                            <i><VueFeather type="arrow-up-right" strokeWidth="4" size="0.8rem" class="toprightarrow-primary me-2 ms-1 mt-1" /></i>Buka
                                        </RouterLink>
                                        <div class="parrten">
                                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 448.057 448.057" style="enable-background:new 0 0 448.057 448.057;" xml:space="preserve">
                                                <g><g><path d="M404.562,7.468c-0.021-0.017-0.041-0.034-0.062-0.051c-13.577-11.314-33.755-9.479-45.069,4.099 c-0.017,0.02-0.034,0.041-0.051,0.062l-135.36,162.56L88.66,11.577C77.35-2.031,57.149-3.894,43.54,7.417                                            c-13.608,11.311-15.471,31.512-4.16,45.12l129.6,155.52h-40.96c-17.673,0-32,14.327-32,32s14.327,32,32,32h64v144                                            c0,17.673,14.327,32,32,32c17.673,0,32-14.327,32-32v-180.48l152.64-183.04C419.974,38.96,418.139,18.782,404.562,7.468z"></path></g></g><g><g><path d="M320.02,208.057h-16c-17.673,0-32,14.327-32,32s14.327,32,32,32h16c17.673,0,32-14.327,32-32 S337.694,208.057,320.02,208.057z"></path></g></g>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mx-auto py-4">
                                <img src="/assets/img/logo-densus.png" class="img-fluid" alt="" />
                            </div>
                        </div>
                        <div class="d-md-none row align-items-center justify-content-center pb-5">
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
    </div>
</template>
<style scoped>

.bg-home {
    background-image: url('/assets/img/home-bg2.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
}

.income-card:not(:hover) .round-box :deep(svg) {
    fill: transparent!important;
}

#card-monitoring {
    -webkit-animation: move2 1.3s infinite alternate;
    animation: move2 1.3s infinite alternate;
}

#card-gepee {
    -webkit-animation: move2 1.5s infinite alternate;
    animation: move2 1.5s infinite alternate;
}

.card-welcome {
    border-color: #09e0b7;
}

.logo-support:first-child img {
    width: 3.5rem;
}

.logo-support:nth-child(2) img {
    width: 9rem;
}

@media (min-width >= 768px) {

    .logo-support:first-child img {
        width: 3.5vw;
    }

    .logo-support:nth-child(2) img {
        width: 9vw;
    }

} 

</style>