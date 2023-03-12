<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import Listbox from "primevue/listbox";

const emit = defineEmits(["change"]);

const props = defineProps({
    inputId: { type: String, default: "inputDivre" },
    inputPlaceholder: { type: String, default: "Pilih Regional" },
    isRequired: { type: Boolean, default: false },
    defaultValue: { default: null },
    valueKey: { type: String, required: true },
    labelKey: { type: String, required: true },
    useResetItem: { type: Boolean, default: false },
    resetTitle: { type: String, default: "Reset Selection" }
});

const list = ref([]);

const selected = ref(props.defaultValue);
const title = computed(() => {
    const index = list.value.findIndex(item => item[props.valueKey] == selected.value);
    return (index < 0) ? null : list.value[index][props.labelKey];
});

const showList = ref(false);
const isLoading = ref(false);
const disable = ref(false);
const isDisabled = computed(() => isLoading.value || list.value.length < 1 || disable.value);

const onBodyClick = () => showList.value = false;
onMounted(() => document.body.addEventListener("click", onBodyClick));
onUnmounted(() => document.body.addEventListener("click", onBodyClick));

const hasValidated = ref(false);
const validate = () => hasValidated.value = true;

const fetch = async (callApi, callback = null) => {
    isLoading.value = true;
    list.value = [];
    
    const resetList = {};
    resetList[props.valueKey] = null;
    resetList[props.labelKey] = props.resetTitle;
    
    let dataFetch = await callApi();
    
    if(!Array.isArray(dataFetch))
        dataFetch = [dataFetch];

    if(props.useResetItem)
        dataFetch = [ resetList, ...dataFetch ];

    list.value = dataFetch;
    isLoading.value = false;

    callback && callback(dataFetch);
};

const setValue = val => {
    selected.value = val;
};

const setDisabled = val => {
    disable.value = val;
};

defineExpose({ validate, fetch, setValue, setDisabled });

const onChange = e => {
    emit("change", e.value);
    showList.value = false;
};
</script>
<template>
    <div class="position-relative">
        <input type="text" :value="title" @click.stop="showList = true" :id="inputId" :class="{ 'is-invalid': isRequired && hasValidated && !selected }" class="form-control" :placeholder="isLoading ? 'Loading' : inputPlaceholder" readonly :disabled="isDisabled" />
        <div v-if="isLoading" class="position-absolute top-50 end-0 translate-middle mr-2 d-flex align-items-center">
            <VueFeather type="loader" animation="spin" size="1rem" />
        </div>
        <div v-if="showList" class="listbox-wrapper">
            <Listbox v-model="selected" @change="onChange" @click.stop="" :options="list"
                :optionValue="valueKey" :optionLabel="labelKey" :filter="true"
                :filterPlaceholder="inputPlaceholder" :disabled="isDisabled" />
        </div>
    </div>
</template>