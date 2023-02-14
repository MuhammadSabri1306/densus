<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { useUserStore } from "@stores/user";
import { useLocationStore } from "@stores/location";
import Listbox from "primevue/listbox";

const emit = defineEmits(["divreChange", "witelChange"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: false },
    requireWitel: { type: Boolean, default: false }
});

const isDivreRequired = computed(() => props.requireDivre);
const isWitelRequired = computed(() => props.requireWitel);

const data = {
    divre: null,
    witel: null
};

const userStore = useUserStore();
const locationStore = useLocationStore();

const userLevel = computed(() => userStore.level);
const userLocationId = computed(() => userStore.locationId);

const divreList = computed(() => locationStore.divreList);
const isDivreListLoading = ref(true);
const disableDivre = ref(false);
const isDivreDisabled = computed(() => divreList.length < 1 || isDivreListLoading.value || isDivreDisabled.value);
locationStore.fetchDivre(false, response => {
    isDivreListLoading.value = false;
    if(!response.success || userLevel.value == "nasional")
        return;
    
});




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