<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useDataForm } from "@helpers/data-form";
import { required } from "@vuelidate/validators";
import http from "@helpers/http-common";
import Listbox from "primevue/listbox";

const emit = defineEmits(["divreChange", "witelChange"]);
const props = defineProps({
    fieldRequired: { type: Boolean, default: false },
    defaultDivre: { type: String, default: null },
    defaultWitel: { type: String, default: null }
});

const initDivre = { value: props.defaultDivre };
if(props.fieldRequired)
    initDivre.required = required;

const initWitel = { value: props.defaultWitel };
if(props.fieldRequired)
    initWitel.required = required;

const { data, v$ } = useDataForm({
    divre: initDivre,
    witel: initWitel
});

const divreList = ref([]);
const divreTitle = computed(() => {
    const index = divreList.value.findIndex(item => item.divre_code == data.divre);
    return (index < 0) ? null : divreList.value[index].divre_name;
});
const disableDivreListbox = computed(() => divreList.value.length < 1);
const isDivreListLoading = ref(true);
const showDivreList = ref(false);

http.get("/monitoring/divre")
    .then(response => {
        const data = response.data.divre;
        divreList.value = data || [];

        if(!props.defaultDivre)
            return;
        isWitelListLoading.value = true;
        http.get("/monitoring/witel/" + props.defaultDivre)
            .then(response => {
                const data = response.data.witel;
                witelList.value = data || [];
            })
            .catch(err => console.error(err))
            .finally(() => isWitelListLoading.value = false);
    })
    .catch(err => console.error(err))
    .finally(() => isDivreListLoading.value = false);

const witelList = ref([]);
const witelTitle = computed(() => {
    const index = witelList.value.findIndex(item => item.witel_code == data.witel);
    return (index < 0) ? null : witelList.value[index].witel_name;
});
const disableWitelListbox = computed(() => witelList.value.length < 1);
const isWitelListLoading = ref(false);
const showWitelList = ref(false);

const fetchWitel = divreCode => {
    data.witel = null;
    witelList.value = [];
    isWitelListLoading.value = true;
    showWitelList.value = false;

    http.get("/monitoring/witel/" + divreCode)
        .then(response => {
            const data = response.data.witel;
            witelList.value = data || [];
        })
        .catch(err => console.error(err))
        .finally(() => isWitelListLoading.value = false);
};

const onFilterDivreChange = event => {
    showDivreList.value = false;
    emit("divreChange", {
        divreCode: data.divre,
        divreName: divreTitle.value
    });
    fetchWitel(event.value);
};

const onFilterWitelChange = event => {
    showWitelList.value = false;
    emit("witelChange", {
        witelCode: data.witel,
        witelName: witelTitle.value
    });
};

const onBodyClick = () => {
	showDivreList.value = false;
	showWitelList.value = false;
};

onMounted(() => document.body.addEventListener("click", onBodyClick));
onUnmounted(() => document.body.addEventListener("click", onBodyClick));

const hasValidated = ref(false);
const validate = () => {
    hasValidated.value = true;
    v$.value.$validate();
};
defineExpose({ validate });
</script>
<template>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-2">
                <label for="inputRegional" class="col-form-label">Regional<span v-if="fieldRequired" class="text-danger"> *</span></label>
                <div class="position-relative">
                    <input type="text" :class="{ 'is-invalid': fieldRequired && hasValidated && v$.divre.$invalid }" :value="divreTitle" @click.stop="showDivreList = true" id="inputRegional" class="form-control" :placeholder="isDivreListLoading ? 'Loading' : 'Pilih Regional'" readonly :disabled="disableDivreListbox" />
                    <div v-if="isDivreListLoading" class="position-absolute top-50 end-0 translate-middle mr-2 d-flex align-items-center">
                        <VueFeather type="loader" animation="spin" size="1rem" />
                    </div>
                    <div v-if="showDivreList" class="listbox-wrapper">
                        <Listbox v-model="data.divre" @change="onFilterDivreChange" @click.stop="" :options="divreList" optionValue="divre_code" optionLabel="divre_name" :filter="true" filterPlaceholder="Pilih Regional" :disabled="disableDivreListbox" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <label for="inputWitel" class="col-form-label">Witel<span v-if="fieldRequired" class="text-danger"> *</span></label>
                <div class="position-relative">
                    <input type="text" :value="witelTitle" @click.stop="showWitelList = true" id="inputWitel" :class="{ 'is-invalid': fieldRequired && hasValidated && v$.divre.$invalid }" class="form-control" :placeholder="isWitelListLoading ? 'Loading' : 'Pilih Witel'" readonly :disabled="disableWitelListbox" />
                    <div v-if="isWitelListLoading" class="position-absolute top-50 end-0 translate-middle mr-2 d-flex align-items-center">
                        <VueFeather type="loader" animation="spin" size="1rem" />
                    </div>
                    <div v-if="showWitelList" class="listbox-wrapper">
                        <Listbox v-model="data.witel" @change="onFilterWitelChange" @click.stop="" :options="witelList" optionValue="witel_code" optionLabel="witel_name" :filter="true" filterPlaceholder="Pilih Witel" :disabled="disableWitelListbox" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>