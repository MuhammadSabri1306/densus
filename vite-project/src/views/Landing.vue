<script setup>
import { computed } from "vue";
import { useUserStore } from "@/stores/user";

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
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h2>DENSUS TR VII</h2><span><h4><b>Mari Mulai Perjalanan {{ name }} dari {{ location }}</b></h4></span>
                        </div>
                        <div class="card-body btn-showcase">
                            <RouterLink to="/monitoring" class="btn btn-primary btn-lg">Mulai</RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>