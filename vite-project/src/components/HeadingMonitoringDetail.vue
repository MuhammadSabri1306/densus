<script setup>
import { ref } from "vue";
import Skeleton from "primevue/skeleton";

const isResolve = ref(false);
const currRtu = ref({});

defineExpose({
    resolve: rtu => {
        if(rtu) {
            currRtu.value = {
                name: rtu.NAMA_RTU,
                location: rtu.LOKASI,
                datelName: rtu.DATEL,
                witelName: rtu.WITEL,
                divreName: rtu.DIVRE
            };
        }
        isResolve.value = true;
    }
});
</script>
<template>
    <div>
        <h3 v-if="isResolve">
            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
            <span class="middle ms-3">{{ currRtu.location }}</span>
        </h3>
        <Skeleton v-else height="1.5rem" width="12rem" class="mb-2" />
        <div v-if="isResolve" class="d-inline-flex align-items-center ms-4">
            <span>{{ currRtu.divreName }}</span>
            <VueFeather type="chevrons-right" size="1.2rem" class="mx-2" />
            <span>{{ currRtu.witelName }}</span>
        </div>
        <Skeleton v-else height="1rem" width="20rem" />
    </div>
</template>