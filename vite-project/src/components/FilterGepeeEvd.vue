<script setup>
import { ref, computed } from "vue";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import Calendar from "primevue/calendar";
import { getYearConfig } from "@/configs/filter";

const emit = defineEmits(["apply"]);
defineProps({
    isRequired: { type: Boolean, default: false }
});

const yearConfig = getYearConfig();
const gepeeEvdStore = useGepeeEvdStore();

const selectedDate = ref(new Date(gepeeEvdStore.filters.year.toString()));
const isYearInvalid = computed(() => {
    const year = selectedDate.value.getFullYear();
    return year < yearConfig.startYear || year > yearConfig.endYear;
});

const selectedSemester = ref(gepeeEvdStore.filters.semester);
const onSubmit = () => {
    gepeeEvdStore.setFilter({
        semester: selectedSemester.value,
        year: selectedDate.value.getFullYear()
    });
    emit("apply");
};
</script>
<template>
    <form @submit.prevent="onSubmit">
        <div class="row justify-content-end align-items-center gx-5 gy-4">
            <div class="col-auto">
                <div class="d-flex align-items-center justify-content-end">
                    <label for="inputYear">Tahun</label>
                    <Calendar v-model="selectedDate" view="year" dateFormat="yy" placeholder="Pilih Tahun" inputId="inputYear"
                        :class="{ 'p-invalid': isYearInvalid }" inputClass="form-control ms-2 mb-2" panelClass="filter-year-panel" />
                </div>
                <small v-if="isYearInvalid" class="text-danger">Tahun yang dipilih belum tersedia</small>
            </div>
            <div class="col-auto">
                <div class="d-flex align-items-center">
                    <label>Semester</label>
                    <div class="ms-4">
                        <input type="radio" v-model="selectedSemester" :value="1" id="radioSemester1" class="radio_animated" />
                        <label for="radioSemester1">I</label>
                    </div>
                    <div class="ms-4">
                        <input type="radio" v-model="selectedSemester" :value="2" id="radioSemester2" class="radio_animated" />
                        <label for="radioSemester2">II</label>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" :disabled="isYearInvalid" class="btn btn-primary mb-2">Terapkan</button>
            </div>
        </div>
    </form>
</template>
<style scoped>

:deep(.form-control) {
    width: 6rem!important;
}

</style>