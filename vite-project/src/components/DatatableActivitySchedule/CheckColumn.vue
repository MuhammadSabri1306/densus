<script setup>
import { computed } from "vue";

const emit = defineEmits(["change"]);
const props = defineProps({
    rowIndex: { type: Number, required: true },
    rowData: { type: Array, required: true }
});

const data = computed(() => props.rowData);
const onCheckboxChange = ({ fieldKey, value }) => {
    value = !value;
    emit("change", { rowIndex: props.rowIndex, fieldKey, value });
};
</script>
<template>
    <td v-for="item in data" class="text-center middle">
        <input type="checkbox" :checked="item.value" @change.prevent="onCheckboxChange(item)"
            :disabled="item.isDisabled" :aria-label="item.label" class="form-check-input" />
    </td>
</template>