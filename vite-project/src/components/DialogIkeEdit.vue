<script setup>
import { ref, computed } from "vue";
import { useIkeStore } from "@/stores/ike";
import { useViewStore } from "@stores/view";
import { toRoman } from "@helpers/number-format";
import { required, integer, decimal } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import Dialog from "primevue/dialog";
import InputNumber from "@/components/ui/InputNumber.vue";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    location: { type: Object, default: {} },
    ike: { type: Object, default: {} }
});

const showDialog = ref(true);

const { data, v$ } = useDataForm({
    kwh_usage: { value: props.ike?.kwh_usage, required, integer },
    area_value: { value: props.ike?.area_value, required, decimal }
});

const ikeStore = useIkeStore();
const viewStore = useViewStore();
const id = computed(() => props.ike?.id);

const locationName = computed(() => props.location?.sto_name);

const ikeDateTexts = computed(() => {
    const ike = props.ike;
    const monthList = ikeStore.monthList;

    if(!ike.created_at)
        return null;
    
    const date = new Date(ike.created_at);
    const year = date.getFullYear();
    const week = toRoman(ike.week);

    const monthIndex = date.getMonth();
    const month = monthList[monthIndex]?.name;

    return { year, month, week };
});

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        kwh_usage: data.kwh_usage,
        area_value: data.area_value
    };
    
    isLoading.value = true;
    ikeStore.update(id.value, body, response => {
        if(response.success) {
            viewStore.showToast("Data IKE", "Berhasil update item.", true);
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
    <Dialog header="Form Update IKE" v-model:visible="showDialog" maximizable modal draggable
        class="dialog-basic" contentClass="tw-overflow-x-hidden" @afterHide="$emit('close')">
        <div class="pt-4 pt-lg-2 pb-0 px-md-4">
            <h5 v-if="locationName">{{ locationName }}</h5>
            <p v-if="ikeDateTexts">{{ ikeDateTexts.month }} {{ ikeDateTexts.year }} Minggu {{ ikeDateTexts.week }}</p>
            <form @submit.prevent="onSubmit">
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