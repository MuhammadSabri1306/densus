<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from "vue";
import { useLocationStore } from "@stores/location";
import Listbox from "primevue/listbox";

const emit = defineEmits(["divreChange", "witelChange"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: false },
    requireWitel: { type: Boolean, default: false },
    defaultDivre: { type: String, default: null },
    defaultWitel: { type: String, default: null },
    disableDivre: { type: Boolean, default: false },
    disableWitel: { type: Boolean, default: false }
});

const isDivreRequired = ref(props.requireDivre);
const isWitelRequired = ref(props.requireWitel);
const isDivreDisabled = ref(props.disableDivre);
const isWitelDisabled = ref(props.disableWitel);

const locationStore = useLocationStore();
const data = reactive({
    divre: props.defaultDivre,
    witel: props.defaultWitel
});

watch(() => props.defaultDivre, divre => data.divre = divre);
watch(() => props.defaulWitel, witel => data.witel = witel);

const divreList = computed(() => locationStore.divreList);
const divreTitle = computed(() => {
    const index = divreList.value.findIndex(item => item.divre_code == data.divre);
    return (index < 0) ? null : divreList.value[index].divre_name;
});
const disableDivreListbox = computed(() => divreList.value.length < 1 || isDivreDisabled.value);
const isDivreListLoading = ref(true);
const showDivreList = ref(false);

const witelList = computed(() => locationStore.witelList);
const witelTitle = computed(() => {
    const index = witelList.value.findIndex(item => item.witel_code == data.witel);
    return (index < 0) ? null : witelList.value[index].witel_name;
});
const disableWitelListbox = computed(() => witelList.value.length < 1 || isWitelDisabled.value);
const isWitelListLoading = ref(false);
const showWitelList = ref(false);

locationStore.fetchDivre(false, response => {
    isDivreListLoading.value = false;
    if(!response.success || !data.divre)
        return;
    console.log(data.divre);
    isWitelListLoading.value = true;
    locationStore.fetchWitel(data.divre, () => {
        isWitelListLoading.value = false;
    });
});

const refetchWitel = divreCode => {
    data.witel = null;
    isWitelListLoading.value = true;
    showWitelList.value = false;

    console.log(data.divre);
    locationStore.fetchWitel(data.divre, () => {
        isWitelListLoading.value = false;
    });
};

const onFilterDivreChange = event => {
    showDivreList.value = false;
    emit("divreChange", {
        divreCode: data.divre,
        divreName: divreTitle.value
    });
    refetchWitel(event.value);
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
const validate = () => hasValidated.value = true;
defineExpose({ validate });
</script>
<template>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-2">
                <label for="inputRegional" class="col-form-label">Regional<span v-if="isDivreRequired" class="text-danger"> *</span></label>
                <div class="position-relative">
                    <input type="text" :class="{ 'is-invalid': isDivreRequired && hasValidated && !data.divre }" :value="divreTitle" @click.stop="showDivreList = true" id="inputRegional" class="form-control" :placeholder="isDivreListLoading ? 'Loading' : 'Pilih Regional'" readonly :disabled="disableDivreListbox" />
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
                <label for="inputWitel" class="col-form-label">Witel<span v-if="isWitelRequired" class="text-danger"> *</span></label>
                <div class="position-relative">
                    <input type="text" :value="witelTitle" @click.stop="showWitelList = true" id="inputWitel" :class="{ 'is-invalid': isWitelRequired && hasValidated && !data.witel }" class="form-control" :placeholder="isWitelListLoading ? 'Loading' : 'Pilih Witel'" readonly :disabled="disableWitelListbox" />
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