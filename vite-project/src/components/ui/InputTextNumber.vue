<script setup>
import { ref, computed, watchEffect, onMounted } from "vue";
import { toNumberText } from "@/helpers/number-format";

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const numberValue = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    }
});

const numberSaved = ref(props.modelValue);
watchEffect(numberSaved, val => numberValue.value = Number(val));

const numberText = ref(null);

const onKeyPress = event => {
    const updatedValue = event.target.value;
    numberSaved.value = updatedValue.replaceAll(".", "")
        .replaceAll(",", ".")
        .replace(/[^0-9.,]/g, "");
    numberText.value = numberSaved.value ? toNumberText(numberSaved.value, 50) : null;
    event.target.value = numberText.value;
};

onMounted(() => {
    numberText.value = numberSaved.value ? toNumberText(numberSaved.value, 50) : null;
});
</script>
<template>
    <input type="text" :value="numberText" @change="test" @keyup.prevent="onKeyPress" />
</template>