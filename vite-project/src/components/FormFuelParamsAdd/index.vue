<script setup>
import { ref, reactive } from "vue";
import { usePlnStore } from "@stores/pln";
import { useViewStore } from "@stores/view";
import SectionLocation from "./Location.vue";
import SectionPort from "./Port.vue";
import SectionSend from "./Send.vue";

const emit = defineEmits(["save", "cancel"]);

const currPage = ref(1);
const stepsPage = ["Lokasi", "Port", "Simpan"];

const data = reactive({
    divre_kode: null,
    divre_name: null,
    witel_kode: null,
    witel_name: null,
    sto_kode: null,
    lokasi: null,
    alamat: null,
    rtu_kode: null,
    rtu_name: null,
    genset_merk: null,
    genset_type: null,
    genset_daya: null,
    port_deg1_low: null,
    port_deg1_high: null,
    port_deg2_low: null,
    port_deg2_high: null,
    port_status_genset: null,
    port_status_pln: null
});

const onLocationUpdate = pageData => {
    data.divre_name = pageData.data.divre_name;
    data.divre_kode = pageData.data.divre_kode;
    data.witel_kode = pageData.data.witel_kode;
    data.witel_name = pageData.data.witel_name;
    data.sto_kode = pageData.data.sto_kode;
    data.lokasi = pageData.data.lokasi;
    data.alamat = pageData.data.alamat;
    data.rtu_kode = pageData.data.rtu_kode;
    data.rtu_name = pageData.data.rtu_name;
    data.genset_merk = pageData.data.genset_merk;
    data.genset_type = pageData.data.genset_type;
    data.genset_daya = pageData.data.genset_daya;

    currPage.value = pageData.navigateTo;
};

const onPortUpdate = pageData => {
    data.port_deg1_low = pageData.data.port_deg1_low;
    data.port_deg1_high = pageData.data.port_deg1_high;
    data.port_deg2_low = pageData.data.port_deg2_low;
    data.port_deg2_high = pageData.data.port_deg2_high;
    data.port_status_genset = pageData.data.port_status_genset;
    data.port_status_pln = pageData.data.port_status_pln;

    currPage.value = pageData.navigateTo;
};

const plnStore = usePlnStore();
const viewStore = useViewStore();
const isLoading = ref(false);

const onSubmit = () => {
    const body = {
        divre_name: data.divre_name,
        divre_kode: data.divre_kode,
        witel_kode: data.witel_kode,
        witel_name: data.witel_name,
        sto_kode: data.sto_kode,
        lokasi: data.lokasi,
        alamat: data.alamat,
        rtu_kode: data.rtu_kode,
        rtu_name: data.rtu_name,
        genset_merk: data.genset_merk,
        genset_type: data.genset_type,
        genset_daya: data.genset_daya,
        port_deg1_low: data.port_deg1_low,
        port_deg1_high: data.port_deg1_high,
        port_deg2_low: data.port_deg2_low,
        port_deg2_high: data.port_deg2_high,
        port_status_genset: data.port_status_genset,
        port_status_pln: data.port_status_pln
    };
    
    isLoading.value = true;
    plnStore.createParams(body, response => {
        isLoading.value = false;
        if(!response.success)
            return;
        viewStore.showToast("Parameter PLN", "Berhasil menyimpan data bulan ini.", true);
        emit("save");
    });
};

const onSectionSendNavigate = pageNumber => currPage.value = pageNumber;
</script>
<template>
    <form @submit.prevent="onSubmit">
        <KeepAlive>
            <SectionLocation v-if="currPage === 1" :stepsPage="stepsPage" useValidation @update="onLocationUpdate" />
            <SectionPort v-else-if="currPage === 2" :stepsPage="stepsPage" useValidation @update="onPortUpdate" />
            <SectionSend v-else :stepsPage="stepsPage" :data="data" @navigate="onSectionSendNavigate" />
        </KeepAlive>
        <div class="d-flex align-items-end mt-5">
            <button type="submit" v-if="currPage === 3" :class="{ 'btn-loading': isLoading }" class="btn btn-success btn-lg">Simpan</button>
            <button type="button" @click="$emit('cancel')" class="btn btn-secondary ms-auto">Batalkan</button>
        </div>
    </form>
</template>