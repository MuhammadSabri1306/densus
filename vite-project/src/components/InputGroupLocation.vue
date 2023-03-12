<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
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
    witelValue: String
});

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

const onChange = () => {
    const data = {};
    if(!props.useLevel || level.value != "nasional" || currUser.value.level != "nasional") {
        data.divre_kode = location.divre;
        const divreIndex = divreList.value.findIndex(item => item.divre_kode && item.divre_kode == data.divre_kode);
        data.divre_name = divreIndex < 0 ? null : divreList.value[divreIndex].divre_name;
    }

    if(!props.useLevel || level.value == "witel" || currUser.value.level == "witel") {
        data.witel_kode = location.witel;
        const witelIndex = witelList.value.findIndex(item => item.witel_kode && item.witel_kode == data.witel_kode);
        data.witel_name = witelIndex < 0 ? null : witelList.value[witelIndex].witel_name;
    }

    emit("change", data);
};

watch(() => level.value, onChange);

const onDivreChange = val => {
    location.divre = val;
    listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val));
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

        listboxDivre.value.setValue(location.divre);
        onChange();

        listboxWitel.value.fetch(
            () => viewStore.getWitelByDivre(location.divre),
            list => {
                witelList.value = list;
                onChange();
            }
        );
        
    } else if(currLevel == "witel" && location.witel) {
        
        listboxWitel.value.setValue(location.witel);
        onChange();

        listboxWitel.value.fetch(
            () => viewStore.getWitel(location.witel),
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
        () => viewStore.getDivre(),
        list => {
            divreList.value = list;
            onChange();
        }
    );

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
        isValid = !props.requireDivre || location.divre;
    }

    if(showListbox.value.witel) {
        listboxWitel.value.validate();
        isValid = (isValid && props.requireWitel) || (isValid && location.witel);
    }

    return isValid;
};

defineExpose({ validate });
</script>
<template>
    <div class="row justify-content-end align-items-end">
        <div v-show="showListbox.divre" :class="{ 'col-md-6': showListbox.witel }">
            <div class="mb-4">
                <label for="inputDivre" class="col-form-label">Regional<span v-if="requireDivre" class="text-danger"> *</span></label>
                <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                    isRequired valueKey="divre_kode" labelKey="divre_name" @change="onDivreChange" />
            </div>
        </div>
        <div v-show="showListbox.witel" :class="{ 'col-md-6': showListbox.divre }">
            <div class="mb-4">
                <label for="inputWitel" class="col-form-label">Witel<span v-if="requireWitel" class="text-danger"> *</span></label>
                <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                    valueKey="witel_kode" labelKey="witel_name" @change="onWitelChange" />
            </div>
        </div>
    </div>
</template>