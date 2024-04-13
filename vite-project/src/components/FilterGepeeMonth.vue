<script setup>
import { ref, computed, watch } from "vue";
import { useViewStore } from "@/stores/view";
import { getYearConfig } from "@/configs/filter";
import Calendar from "primevue/calendar";

const emit = defineEmits(["apply"]);
const props = defineProps({
    required: { type: Boolean, default: false },
    labelClass: String,
    fieldClass: String,
    btnClass: { type: String, default: "btn btn-primary" }
});

const dateVal = ref(null);
const viewStore = useViewStore();

const setFilter = filters => {
    if(!filters.month)
        return;
    
    if(!dateVal.value)
        dateVal.value = new Date();

    if(filters.year)
        dateVal.value.setFullYear(filters.year);
    dateVal.value.setMonth(filters.month - 1);
};

const watcherSrc = () => {
    const month = viewStore.filters.month;
    const year = viewStore.filters.year;
    return { month, year };
};

watch(watcherSrc, setFilter);

const yearConfig = getYearConfig();
const isDateInvalid = computed(() => {
    if(props.required && !dateVal.value)
        return true;
    if(!dateVal.value)
        return false;
    const year = dateVal.value.getFullYear();
    return year < yearConfig.startYear || year > yearConfig.endYear;
});

const getLabelClass = () => {
    const classList = [];
    if(props.required)
        classList.push("required");
    if(props.labelClass)
        classList.push(props.labelClass);
    return classList.join(" ");
};

const reactiveFieldClass = computed(() => {
    const isInvalid = isDateInvalid.value;
    const classList = [];
    if(isInvalid)
        classList.push("p-invalid");
    if(props.fieldClass)
        classList.push(props.fieldClass);
    return classList.join(" ");
});

const setupValue = () => setFilter(watcherSrc());
const getValue = () => {
    const month = dateVal.value ? dateVal.value.getMonth() + 1 : null;
    const year = dateVal.value ? dateVal.value.getFullYear() : null;
    return { month, year };
};

defineExpose({ setupValue, getValue });
setupValue();

const onSubmit = () => {
    if(!isDateInvalid.value)
        emit("apply", getValue());
};
</script>
<template>
    <form @submit.prevent="onSubmit">
        <label for="inputMonth" :class="getLabelClass()">Bulan</label>
        <Calendar v-model="dateVal" view="month" dateFormat="M yy" :showButtonBar="!required" placeholder="Pilih Bulan"
            :class="reactiveFieldClass" inputId="inputMonth" inputClass="form-control" panelClass="filter-month" />
        <button type="submit" :disabled="isDateInvalid" :class="btnClass">Terapkan</button>
    </form>
</template>