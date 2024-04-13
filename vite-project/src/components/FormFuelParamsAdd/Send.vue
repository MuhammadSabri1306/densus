<script setup>
import { computed } from "vue";
import Steps from "@/components/ui/Steps.vue";

defineEmits(["navigate"]);
const props = defineProps({
    data: { type: Object, required: true },
    stepsPage: { type: Array, default: [] }
});

const dataList = computed(() => {
    const propsData = props.data;
    const dataOrder = ["divre_name", "witel_name", "sto_kode", "lokasi", "alamat",
        "rtu_kode", "rtu_name", "genset_merk", "genset_type", "genset_daya",
        "port_deg1_low", "port_deg1_high", "port_deg2_low", "port_deg2_high",
        "port_status_genset", "port_status_pln"];
    const titles = ["Regional", "Witel", "Kode STO", "Lokasi", "Alamat",
        "Kode RTU", "Nama RTU", "Merk Genset", "Type Genset", "Daya Genset",
        "Port DEG 1 (Low)", "Port DEG 1 (High)", "Port DEG 2 (Low)", "Port DEG 2 (High)",
        "Port Status Digital Genset", "Port Status Digital PLN"];
    
    return dataOrder.map((key, index) => {
        const value = propsData[key];
        const title = titles[index];
        return { value, title };
    });
});
</script>
<template>
    <div class="row">
        <div class="col-12">
            <Steps :steps="stepsPage" :currPage="3" @navigate="pageNumber => $emit('navigate', pageNumber)" class="mb-4" aria-label="Form Input PLN Parameter" />
        </div>
        <div class="col-12">
            <table class="table text-muted">
                <tbody>
                    <tr v-for="item in dataList">
                        <th class="text-muted">{{ item.title }}</th>
                        <td>{{ item.value }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>