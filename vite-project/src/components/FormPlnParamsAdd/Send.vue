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
        "rtu_kode", "rtu_name", "daya", "port_kwh1_low", "port_kwh1_high", "port_kwh2_low",
        "port_kwh2_high", "port_kva", "port_kw", "port_power_factor" ];
    const titles = ["Regional", "Witel", "Kode STO", "Lokasi", "Alamat",
        "Kode RTU", "Nama RTU", "Daya", "Port KWH 1 Low", "Port KWH 1 Hight", "Port KWH 2 Low",
        "Port KWH 2 High", "Port KVA", "Port KW", "Port Power Factor" ];
    
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