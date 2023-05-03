<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from "vue";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import ListboxFilter from "@components/ListboxFilter.vue";

const emit = defineEmits(["change"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: false },
    requireWitel: { type: Boolean, default: false },
    useLevel: { type: Boolean, default: false },
    level: String,
    divreValue: String,
    witelValue: String,
    locationType: { type: String, default: "basic" }
});

const locationTypeList = ["basic", "gepee"];
const fetchKey = (locationTypeList.indexOf(props.locationType) < 0) ? locationTypeList[0] : props.locationType;

const level = computed(() => props.level);

const userStore = useUserStore();
const currUser = computed(() => {
    const level = userStore.level;
    const location = userStore.location;
    const locationId = userStore.locationId;
    return { level, location, locationId };
});

const viewStore = useViewStore();
const divreList = ref([]);
const witelList = ref([]);

const location = reactive({
    divre: null,
    witel: null
});

const listboxDivre = ref(null);
const listboxWitel = ref(null);

const divreName = computed(() => {
    const list = divreList.value;
    const code = location.divre;
    const index = list.findIndex(item => item.divre_kode && item.divre_kode == code);
    return index < 0 ? null : list[index].divre_name;
});

const witelName = computed(() => {
    const list = witelList.value;
    const code = location.witel;
    const index = list.findIndex(item => item.witel_kode && item.witel_kode == code);
    
    return index < 0 ? null : list[index].witel_name;
});

const onChange = () => {
    const data = {};
    if(!props.useLevel || level.value != "nasional" || currUser.value.level != "nasional") {
        data.divre_kode = location.divre;
        data.divre_name = divreName.value;
    }

    if(!props.useLevel || level.value == "witel" || currUser.value.level == "witel") {
        data.witel_kode = location.witel;
        data.witel_name = witelName.value;
    }
    
    emit("change", data);
};

watch(() => level.value, onChange);

const onDivreChange = val => {
    location.divre = val;
    listboxWitel.value.fetch(
        () => viewStore.getWitelByDivre(val, fetchKey),
        list => {
            witelList.value = list;
        }
    );
    onChange();
};

const onWitelChange = val => {
    location.witel = val;
    onChange();
};

onMounted(() => {
    const currLevel = props.useLevel ? level.value : currUser.value.level;

    if(props.divreValue)
        location.divre = props.divreValue;
    if(props.witelValue)
        location.witel = props.witelValue;
    if(currUser.value.level == "divre")
        location.divre = currUser.value.locationId;
    if(currUser.value.level == "witel")
        location.witel = currUser.value.locationId;

    if(currLevel == "divre") {

        listboxWitel.value.fetch(
            () => viewStore.getWitelByDivre(location.divre, fetchKey),
            list => {
                witelList.value = list;
                onChange();
            }
        );
        
    } else if(currLevel == "witel" || location.witel) {
        
        listboxWitel.value.fetch(
            () => viewStore.getWitel(location.witel, fetchKey),
            data => {
                witelList.value = data;
                onChange();
                if(data.length < 1)
                    return;

                const { divre_kode } = data[0];
                location.divre = divre_kode;
                listboxDivre.value.setValue(divre_kode);
                onChange();
            }
        );

    }

    listboxDivre.value.fetch(
        () => viewStore.getDivre(fetchKey),
        list => {
            divreList.value = list;
            onChange();
        }
    );

    if(location.divre)
        listboxDivre.value.setValue(location.divre);
    if(location.witel)
        listboxWitel.value.setValue(location.witel);

    if(currUser.value.level != "nasional")
        listboxDivre.value.setDisabled(true);
    if(currUser.value.level == "witel")
        listboxWitel.value.setDisabled(true);
});

const showListbox = computed(() => {
    const divre = !props.useLevel || level.value != "nasional";
    const witel = !props.useLevel || level.value == "witel";
    return { divre, witel };
});

const validate = () => {
    let isValid = true;

    if(showListbox.value.divre) {
        listboxDivre.value.validate();
        isValid = props.requireDivre && !location.divre ? false : true;
    }

    if(showListbox.value.witel) {
        listboxWitel.value.validate();
        if(isValid)
            isValid = props.requireWitel && !location.witel ? false : true;
    }

    return isValid ? true : false;
};

defineExpose({ validate });
</script>
<template>
    <div class="row justify-content-end align-items-end">
        <div v-show="showListbox.divre" :class="{ 'col-md-6': showListbox.witel }">
            <div class="mb-4">
                <label for="inputDivre" class="col-form-label">Regional<span v-if="requireDivre" class="text-danger"> *</span></label>
                <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                    :isRequired="requireDivre" valueKey="divre_kode" labelKey="divre_name" @change="onDivreChange" />
            </div>
        </div>
        <div v-show="showListbox.witel" :class="{ 'col-md-6': showListbox.divre }">
            <div class="mb-4">
                <label for="inputWitel" class="col-form-label">Witel<span v-if="requireWitel" class="text-danger"> *</span></label>
                <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                    :isRequired="requireWitel" valueKey="witel_kode" labelKey="witel_name" @change="onWitelChange" />
            </div>
        </div>
    </div>
</template>