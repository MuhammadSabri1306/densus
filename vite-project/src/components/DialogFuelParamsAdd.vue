<script setup>
import { ref } from "vue";
import { useViewStore } from "@/stores/view";
import Dialog from "primevue/dialog";
import FormPlnParamsAdd from "@/components/FormPlnParamsAdd/index.vue";

defineEmits(["close", "save"]);
const showDialog = ref(true);

const viewStore = useViewStore();
const currDate = new Date();
const currYear = currDate.getFullYear();
const currMonthName = viewStore.monthList[currDate.getMonth()]?.name;
</script>
<template>
    <Dialog header="Form Input PLN Parameter" v-model:visible="showDialog" modal draggable @afterHide="$emit('close')" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
        <div class="p-4">
            <h5 class="mb-4">Bulan {{ currMonthName }} {{ currYear }}</h5>
            <FormPlnParamsAdd @cancel="showDialog = false" @save="$emit('save')" />
        </div>
    </Dialog>
</template>