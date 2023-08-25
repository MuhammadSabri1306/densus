<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useIkeStore } from "@/stores/ike";
import { useViewStore } from "@stores/view";
import { required, integer, decimal } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import Dialog from "primevue/dialog";
import InputNumber from "@/components/ui/InputNumber.vue";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    location: { type: Object, default: {} },
    allowWeeks: Array
});

const showDialog = ref(true);
const inputableWeeks = computed(() => props.allowWeeks.map(item => Number(item)));
const isWeekRadioDisabled = value => inputableWeeks.value.indexOf(value) < 0;

const { data, v$ } = useDataForm({
    kwh_usage: { required, integer },
    area_value: { required, decimal },
    week: { value: (inputableWeeks.value.length > 0 ? inputableWeeks.value[0] : 1), required, integer },
});

const ikeStore = useIkeStore();
const viewStore = useViewStore();
const route = useRoute();
const idLocation = computed(() => route.params.locationId);

const locationName = computed(() => props.location.sto_name);
const currYear = ref(new Date().getFullYear());
const currMonthName = computed(() => {
    const monthList = ikeStore.monthList;
    const monthIndex = new Date().getMonth();
    return monthList[monthIndex]?.name;
});

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        id_location: idLocation.value,
        kwh_usage: data.kwh_usage,
        area_value: data.area_value,
        week: data.week,
    };
    
    isLoading.value = true;
    ikeStore.create(body, response => {
        if(response.success) {
            viewStore.showToast("Data IKE", `Berhasil menambahkan item bulan ${ currMonthName.value }.`, true);
            emit("save");
            showDialog.value = false;
        }
        isLoading.value = false;
    });
};

const setupAttrTooltip = (inputKey, errMesage) => {
    const field = v$.value[inputKey];
    const isInvalid = field && field.$invalid && hasSubmitted.value;
    if(!isInvalid)
        return null;
    return {
        value: errMesage,
        class: "tooltip-error-input"
    };
};

const kwhAttrTooltip = inputKey => setupAttrTooltip(inputKey, "Nilai penggunaan energi dalam satuan kWh (tanpa koma)");
const areaValueAttrTooltip = inputKey => setupAttrTooltip(inputKey, "Pastikan menggunakan titik(.) untuk angka desimal");
</script>
<template>
    <Dialog header="Form Input IKE" v-model:visible="showDialog" maximizable modal draggable
        class="dialog-basic" contentClass="tw-overflow-x-hidden" @afterHide="$emit('close')">
        <div class="pt-4 pt-lg-2 pb-0 px-md-4">
            <h5 v-if="locationName">{{ locationName }}</h5>
            <form @submit.prevent="onSubmit">
                <div class="row mb-4 gx-4 gy-2">
                    <div class="col-md-auto form-group">
                        <label>{{ currMonthName }} {{ currYear }} Minggu ke- :</label>
                    </div>
                    <div class="col-md">
                        <div class="row gx-4 gy-2 mb-2">
                            <div class="col-auto">
                                <input type="radio" v-model="v$.week.$model" :value="1" :disabled="isWeekRadioDisabled(1)"
                                    id="radioWeek1" class="radio_animated" />
                                <label for="radioWeek1">I</label>
                            </div>
                            <div class="col-auto">
                                <input type="radio" v-model="v$.week.$model" :value="2" :disabled="isWeekRadioDisabled(2)"
                                    id="radioWeek2" class="radio_animated" />
                                <label for="radioWeek2">II</label>
                            </div>
                            <div class="col-auto">
                                <input type="radio" v-model="v$.week.$model" :value="3" :disabled="isWeekRadioDisabled(3)"
                                    id="radioWeek3" class="radio_animated" />
                                <label for="radioWeek3">III</label>
                            </div>
                            <div class="col-auto">
                                <input type="radio" v-model="v$.week.$model" :value="4" :disabled="isWeekRadioDisabled(4)"
                                    id="radioWeek4" class="radio_animated" />
                                <label for="radioWeek4">IV</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row align-items-end gx-5 gap-y-4 mb-5">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="kwhUsage" class="required">Total Penggunaan Energi <i>(kWh)</i></label>
                            <InputNumber v-model="v$.kwh_usage.$model" :class="{ 'is-invalid': hasSubmitted && v$.kwh_usage.$invalid }"
                                class="form-control" id="kwhUsage" placeholder="Cth. 345000" v-tooltip="kwhAttrTooltip('kwh_usage')" />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="areaValue" class="required">Luas Bangunan <i>(m<sup>2</sup>)</i></label>
                            <InputNumber v-model="v$.area_value.$model" :class="{ 'is-invalid': hasSubmitted && v$.area_value.$invalid }"
                                class="form-control" id="areaValue" placeholder="Cth. 122.5" v-tooltip="areaValueAttrTooltip('area_value')" />
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>