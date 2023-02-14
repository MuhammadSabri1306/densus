<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { useLocationStore } from "@stores/location";
import Listbox from "primevue/listbox";

const emit = defineEmits(["change"]);

const props = defineProps({
    inputId: { type: String, default: "inputWitel" },
    inputPlaceholder: { type: String, default: "Pilih Witel" },
    isRequired: { type: Boolean, default: false },
    divre: { default: null },
    defaultValue: { default: null }
});

const locationStore = useLocationStore();
const list = computed(() => locationStore.witelList);

const selected = ref(props.defaultValue);
const title = computed(() => {
    const index = list.value.findIndex(item => item.witel_code == selected.value);
    return (index < 0) ? null : list.value[index].witel_name;
});

const showList = ref(false);
const isLoading = computed(() => locationStore.isLoadingWitel );
const disable = ref(false);
const isDisabled = computed(() => isLoading.value || list.value.length < 1 || disable.value);

locationStore.fetchWitel(props.divre, response => {
    if(!response.success)
        disable.value = true;
});

const onBodyClick = () => showList.value = false;
onMounted(() => document.body.addEventListener("click", onBodyClick));
onUnmounted(() => document.body.addEventListener("click", onBodyClick));

const hasValidated = ref(false);
const validate = () => hasValidated.value = true;
defineExpose({ validate });

const onChange = e => {
    emit("change", { code: e.value, name: title.value });
    showList.value = false;
};
</script>
<template>
    <div class="position-relative">
        <input type="text" :value="title" @click.stop="showList = true" :id="inputId" :class="{ 'is-invalid': isRequired && hasValidated && !selected }" class="form-control" :placeholder="isLoading ? 'Loading' : inputPlaceholder" readonly />
        <div v-if="isLoading" class="position-absolute top-50 end-0 translate-middle mr-2 d-flex align-items-center">
            <VueFeather type="loader" animation="spin" size="1rem" />
        </div>
        <div v-if="showList" class="listbox-wrapper">
            <Listbox v-model="selected" @change="onChange" @click.stop="" :options="list" optionValue="witel_code" optionLabel="witel_name" :filter="true" :filterPlaceholder="inputPlaceholder" :disabled="isDisabled" />
        </div>
    </div>
</template>