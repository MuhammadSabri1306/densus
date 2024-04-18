<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useViewStore } from "@/stores/view";
import { useUserStore } from "@/stores/user";
import ListboxFilterV2 from "@/components/ListboxFilterV2.vue";

const emit = defineEmits([
    "update:divreValue",
    "update:witelValue",
    "change",
    "init",
]);

const props = defineProps({

    locationType: { type: String, default: "basic" },

    divreValue: { default: null },
    divreLabelKey: { type: String, default: "divre_name" },
    divreValueKey: { type: String, default: "divre_kode" },
    isDivreRequired: { type: Boolean, default: false },
    isDivreDisabled: { type: Boolean, default: false },
    isDivreInvalid: { type: Boolean, default: false },

    witelValue: { default: null },
    witelLabelKey: { type: String, default: "witel_name" },
    witelValueKey: { type: String, default: "witel_kode" },
    isWitelRequired: { type: Boolean, default: false },
    isWitelDisabled: { type: Boolean, default: false },
    isWitelInvalid: { type: Boolean, default: false },

    applyUserLevel: { type: Boolean, default: false },
    useDivre: { type: Boolean, default: true },
    useWitel: { type: Boolean, default: true },
    position: { type: String, default: "top" },

});

const locationTypeList = ["basic", "gepee", "sto_master", "newosase"];
const fetchLocKey = (locationTypeList.indexOf(props.locationType) < 0) ? locationTypeList[0] : props.locationType;

const viewStore = useViewStore();

const divreList = ref([]);
const isListboxDivreLoading = ref(false);
const isListboxDivreDisabled = ref(false);
const disabledListboxDivre = computed(() => props.isDivreDisabled || isListboxDivreDisabled.value);
const isListboxDivreInvalid = computed(() => props.isDivreInvalid);
const getDivreItem = (list, val) => list.find(item => item[props.divreValueKey] == val) || null;
const divreVal = computed({
    get() { return props.divreValue; },
    set(newVal) { emit("update:divreValue", newVal); }
});

const witelList = ref([]);
const isListboxWitelLoading = ref(false);
const isListboxWitelDisabled = ref(false);
const disabledListboxWitel = computed(() => props.isWitelDisabled || isListboxWitelDisabled.value);
const isListboxWitelInvalid = computed(() => props.isWitelInvalid);
const getWitelItem = (list, val) => list.find(item => item[props.witelValueKey] == val) || null;
const witelVal = computed({
    get() { return props.witelValue; },
    set(newVal) { emit("update:witelValue", newVal); }
});

const onChange = () => {
    const divre = getDivreItem(divreList.value, divreVal.value);
    const witel = getWitelItem(witelList.value, witelVal.value);
    emit("change", { divre, witel });
};

const onInit = () => {
    const divre = getDivreItem(divreList.value, divreVal.value);
    const witel = getWitelItem(witelList.value, witelVal.value);
    emit("init", { divre, witel });
};

const onDivreChange = divre => {
    witelList.value = [];
    witelVal.value = null;
    if(divre && divre[props.divreValueKey]) {
        isListboxWitelLoading.value = true;
        viewStore.getWitelByDivre(divre[props.divreValueKey], fetchLocKey)
            .then(wList => witelList.value = wList)
            .catch(err => console.error(err))
            .finally(() => {
                isListboxWitelLoading.value = false;
                onChange();
            });
    } else {
        onChange();
    }
};

const userStore = useUserStore();
(async () => {
    // await new Promise(resolve => setTimeout(resolve, 3000));

    isListboxDivreLoading.value = true;
    isListboxWitelLoading.value = true;
    try {
        if(props.applyUserLevel && userStore.level == "divre") {

            divreVal.value = userStore.locationId;
            isListboxDivreDisabled.value = true;

        } else if(props.applyUserLevel && userStore.level == "witel") {

            let divreDataValue = null;
            const divreData = await viewStore.getDivreByWitel(userStore.locationId, fetchLocKey);
            if(divreData && divreData[props.divreValueKey])
                divreDataValue = divreData[props.divreValueKey];
            if(divreVal.value != divreDataValue)
                divreVal.value = divreDataValue;

            witelVal.value = userStore.locationId;
            isListboxDivreDisabled.value = true;
            isListboxWitelDisabled.value = true;

        }

        const dList = await viewStore.getDivre(fetchLocKey);
        divreList.value = dList;
        isListboxDivreLoading.value = false;

        if(divreVal.value) {
            const wList = await viewStore.getWitelByDivre(divreVal.value, fetchLocKey);
            witelList.value = wList;
        }
        isListboxWitelLoading.value = false;
        onInit();

    } catch(err) {
        console.error(err);
    } finally {
        if(isListboxDivreLoading.value)
            isListboxDivreLoading.value = false;
        if(isListboxWitelLoading.value)
            isListboxWitelLoading.value = false;
    }
})();

const showListbox = computed(() => {
    const divre = props.useDivre;
    const witel = props.useWitel;
    return { divre, witel };
});
</script>
<template>
    <div class="row justify-content-end align-items-end">
        <div v-show="showListbox.divre" :class="{ 'col-md-6': showListbox.witel }">
            <div class="form-group">
                <label for="inputDivre" :class="{ 'required': isDivreRequired }">Regional</label>
                <ListboxFilterV2 v-model:list="divreList" v-model:value="divreVal" @change="onDivreChange" :isLoading="isListboxDivreLoading"
                    :isDisabled="disabledListboxDivre" :isInvalid="isListboxDivreInvalid" :valueKey="divreValueKey"
                    :labelKey="divreLabelKey" :position="position" inputId="inputDivre" inputPlaceholder="Pilih Divre" />
            </div>
        </div>
        <div v-show="showListbox.witel" :class="{ 'col-md-6': showListbox.divre }">
            <div class="form-group">
                <label for="inputWitel" :class="{ 'required': isWitelRequired }">Witel</label>
                <ListboxFilterV2 v-model:list="witelList" v-model:value="witelVal" @change="onChange" :isLoading="isListboxWitelLoading"
                    :isDisabled="disabledListboxWitel" :isInvalid="isListboxWitelInvalid" :valueKey="witelValueKey"
                    :labelKey="witelLabelKey" :position="position" inputId="inputWitel" inputPlaceholder="Pilih Witel" />
            </div>
        </div>
    </div>
</template>