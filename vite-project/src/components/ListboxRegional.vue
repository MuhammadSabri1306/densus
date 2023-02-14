<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useLocationStore } from "@stores/location";
import Listbox from "primevue/listbox";

const emit = defineEmits(["change"]);

const props = defineProps({
    inputId: { type: String, default: "inputDivre" },
    inputPlaceholder: { type: String, default: "Pilih Regional" },
    isRequired: { type: Boolean, default: false },
    defaultValue: { default: null }
});

const locationStore = useLocationStore();
const list = computed(() => locationStore.divreList);

const selected = ref(props.defaultValue);
const title = computed(() => {
    const index = list.value.findIndex(item => item.divre_code == selected.value);
    return (index < 0) ? null : list.value[index].divre_name;
});

const showList = ref(false);
const isLoading = ref(true);
const disable = ref(false);
const isDisabled = computed(() => isLoading.value || list.value.length < 1 || disable.value);

locationStore.fetchDivre(false, response => {
    isLoading.value = false;
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
            <Listbox v-model="selected" @change="onChange" @click.stop="" :options="list" optionValue="divre_code" optionLabel="divre_name" :filter="true" :filterPlaceholder="inputPlaceholder" :disabled="isDisabled" />
        </div>
    </div>
</template>