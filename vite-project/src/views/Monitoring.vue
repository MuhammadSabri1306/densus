<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { useUserStore } from "@stores/user";
import { useLocationStore } from "@stores/location";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
// import ListboxRegWitel from "@components/ListboxRegWitel.vue";
import GridCardRtu from "@components/GridCardRtu.vue";
import ListboxRegional from "@components/ListboxRegional.vue";
import ListboxWitel from "@components/ListboxWitel.vue";

const filters = reactive({
    divre: null,
    witel: null
});

const userStore = useUserStore();
const userLevel = computed(() => userStore.level);
const isLocLoading = ref(true);
const isLocLoaded = ref(false);

const locationStore = useLocationStore();
const listboxRegional = ref(null);
const listboxWitel = ref(null);
const showLbRegional = ref(false);
const showLbWitel = ref(false);

const divreName = ref(null);
const witelName = ref(null);

onMounted(() => {
    if(isLocLoaded.value)
        return;

    userStore.getLocation(response => {
        const location = response.location;
        if(!location)
            return;

        filters.divre = location.divre_code;
        filters.witel = location.witel_code;
        divreName.value = location.divre_name;
        witelName.value = location.witel_name;

        if(userLevel.value != "witel") {

            if(userLevel.value == "nasional")
                showLbRegional.value = true;
            locationStore.fetchWitel(filters.divre, response => {
                console.log(response);
                showLbWitel.value = response.success
            });
        
        } else {

            gridCardRtu.value.fetchRtu(filters.divre, filters.witel);
            
        }

        isLocLoading.value = false;
        isLocLoaded.value = true;
    });
})

const onDivreChange = ({ code, name }) => {
    filters.divre = code;
    divreName.value = name;
    locationStore.fetchWitel(code);
    showLbWitel.value = true;
};

const onWitelChange = ({ code, name }) => {
    filters.witel = code;
    witelName.value = name;
};

const gridCardRtu = ref(null);
const onFilterSubmit = () => {
    gridCardRtu.value.fetchRtu(filters.divre, filters.witel);
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Daftar RTU</h3>
                        <DashboardBreadcrumb :items="['Monitoring RTU', 'Daftar RTU']" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="card-title">Filter</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="onFilterSubmit">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label for="inputRegional" class="col-form-label">Regional<span class="text-danger"> *</span></label>
                                            <ListboxRegional v-if="!isLocLoading && showLbRegional" ref="listboxRegional" :defaultValue="filters.divre" @change="onDivreChange" />
                                            <input type="text" v-else :value="divreName" id="inputRegional" placeholder="Pilih Regional" disabled class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label for="inputWitel" class="col-form-label">Regional<span class="text-danger"> *</span></label>
                                            <ListboxWitel v-if="!isLocLoading && showLbWitel" ref="listboxWitel" :divre="filters.divre" :defaultValue="filters.witel" @change="onWitelChange" />
                                            <input type="text" v-else :value="witelName" id="inputWitel" placeholder="Pilih Witel" disabled class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                <!-- <ListboxRegWitel :defaultDivre="filters.divre" :defaultWitel="filters.witel" @divreChange="onDivreChange" @witelChange="onWitelChange" /> -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" :disabled="!filters.divre || !filters.witel" class="btn btn-primary btn-lg">Cari</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <GridCardRtu ref="gridCardRtu" />
    </div>
</template>