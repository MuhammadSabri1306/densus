<script setup>
import { ref, computed } from "vue";
import { useViewStore } from "@/stores/view";
import Dialog from "primevue/dialog";

const emit = defineEmits(["close"]);
const props = defineProps({
    dataParams: { type: Object, required: true }
});

const showDialog = ref(true);
const viewStore = useViewStore();

const currDate = new Date();
const currYear = currDate.getFullYear();
const currMonthName = viewStore.monthList[currDate.getMonth()]?.name;

const dataList = computed(() => {
    const propsData = props.dataParams;
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
    <Dialog header="Detail PLN Parameter" v-model:visible="showDialog" modal draggable @afterHide="$emit('close')" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <div class="p-4">
            <div>
                <h5 class="mb-4">Bulan {{ currMonthName }} {{ currYear }}</h5>
            </div>
            <div>
                <table class="table text-muted">
                    <tbody>
                        <tr v-for="item in dataList">
                            <th class="text-muted">{{ item.title }}</th>
                            <td>{{ item.value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end align-items-end mt-5">
                <button type="button" @click="showDialog = false" class="btn btn-secondary">Kembali</button>
            </div>
        </div>
    </Dialog>
</template>