<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import Listbox from "primevue/listbox";

const emit = defineEmits([
    "update:list",
    "update:value",
    "change",
]);

const props = defineProps({

    list: { default: null },
    value: { default: null },
    isLoading: { type: Boolean, default: false },
    isDisabled: { type: Boolean, default: false },
    isInvalid: { type: Boolean, default: false },

    valueKey: { type: String, required: true },
    labelKey: { type: String, required: true },

    useResetItem: { type: Boolean, default: false },
    resetTitle: { type: String, default: "Reset Selection" },
    useSearchField: { type: Boolean, default: true },
    position: { type: String, default: "top" },
    inputId: { type: String, default: "" },
    inputPlaceholder: { type: String, default: "Pilih Item" },

});

const data = computed({
    get() { return props.list },
    set(newVal) { emit("update:list", newVal) }
});

const itemValue = computed({
    get() { return props.value },
    set(newVal) { emit("update:value", newVal); }
});

const itemText = computed(() => {
    const list = props.list;
    const value = props.value;
    const selectedItem = list.find(item => item[props.valueKey] == value) || null;
    if(!selectedItem || !selectedItem[props.labelKey])
        return null;
    return selectedItem[props.labelKey];
});

const isInputLoading = computed(() => props.isLoading);
const isInputInvalid = computed(() => props.isInvalid);
const isInputDisabled = computed(() => {
    const isDisabled = props.isDisabled;
    const isLoading = props.isLoading;
    const list = props.list;
    return isDisabled || isLoading || list.length < 1;
});

const showList = ref(false);
const isListShow = computed(() => {
    const show = showList.value;
    const isDisabled = props.isDisabled;
    const isLoading = props.isLoading;
    const list = props.list;
    return show && !isDisabled && !isLoading && list.length > 0;
});

const onChange = ({ value }) => {
    showList.value = false;
    const selectedItem = props.list.find(item => item[props.valueKey] == value) || null;
    nextTick(() => emit("change", selectedItem));
};

const listboxElm = ref(null);
const onBodyClick = event => {
    const elm = listboxElm.value;
    if(elm) {
        const isSelfTarget = elm.contains(event.target);
        if(isSelfTarget)
            showList.value = !showList.value;
        else if(showList.value)
            showList.value = false;
    }
};

onMounted(() => document.body.addEventListener("click", onBodyClick));
onUnmounted(() => document.body.addEventListener("click", onBodyClick));

const wrapperClass = computed(() => {
    const position = props.position;
    if(position == "bottom")
        return "post-bottom";
    return;
});
</script>
<template>
    <div ref="listboxElm" class="position-relative">
        <input type="text" :value="itemText" :disabled="isInputDisabled" readonly :id="inputId"
            :class="{ 'is-invalid': isInputInvalid }" class="form-control"
            :placeholder="isInputLoading ? 'Loading' : inputPlaceholder"
            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
        <div v-if="isInputLoading" class="position-absolute top-50 end-0 translate-middle mr-2 d-flex align-items-center">
            <VueFeather type="loader" animation="spin" size="1rem" />
        </div>
        <div v-if="isListShow" :class="wrapperClass" class="listbox-wrapper">
            <Listbox v-model="itemValue" @change="onChange" @click.stop="" :options="data"
                :optionValue="valueKey" :optionLabel="labelKey" :filter="useSearchField"
                :filterPlaceholder="inputPlaceholder" :disabled="isInputDisabled" />
        </div>
    </div>
</template>