<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import { getYearConfig } from "@/configs/filter";
import { backendUrl } from "@/configs/base";
import ListboxFilter from "@components/ListboxFilter.vue";
import Calendar from "primevue/calendar";
import Dialog from "primevue/dialog";

const emit = defineEmits(["close"]);
const props = defineProps({
    baseUrl: { type: String, required: true },
    title: { type: String, default: "Export Data" },
    useDivre: { type: Boolean, default: false },
    requireDivre: { type: Boolean, default: false },
    useWitel: { type: Boolean, default: false },
    requireWitel: { type: Boolean, default: false },
    useYear: { type: Boolean, default: false },
    requireYear: { type: Boolean, default: false },
    useMonth: { type: Boolean, default: false },
    requireMonth: { type: Boolean, default: false },
    autoApply: { type: Function, default: () => false }
});

const showDialog = ref(true);

const viewStore = useViewStore();
const exportFilter = reactive({
    divre: viewStore.filters.divre,
    witel: viewStore.filters.witel,
    date: null
});

const getFiltersValue = () => {
    const divre = exportFilter.divre;
    const witel = exportFilter.witel;
    
    const month = exportFilter.date ? exportFilter.date.getMonth() + 1 : null;
    const year = exportFilter.date ? exportFilter.date.getFullYear() : null;

    return { divre, witel, month, year };
};

const userStore = useUserStore();
const currUser = computed(() => {
    const level = userStore.level;
    const location = userStore.location;
    const locationId = userStore.locationId;
    return { level, location, locationId };
});

const disableSubmit = computed(() => {
    const divre = exportFilter.divre;
    const witel = exportFilter.witel;
    const date = exportFilter.date;

    if(props.useDivre && props.requireDivre)
        return props.requireDivre && !divre;
    if(props.useWitel && props.requireWitel)
        return props.requireWitel && !witel;
    if(props.useYear && props.requireYear)
        return props.requireYear && !date;
    if(props.useMonth && props.requireMonth)
        return props.requireMonth && !date;
});

const listboxDivre = ref(null);
const listboxWitel = ref(null);

const onDivreChange = val => {
    exportFilter.divre = val;
    listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val, "gepee"));
};

const onWitelChange = val => exportFilter.witel = val;

const yearConfig = getYearConfig();
const isDateInvalid = computed(() => {
    if((props.requireYear || props.requireMonth) && !exportFilter.date)
        return true;
    if(!exportFilter.date)
        return false;
    const year = exportFilter.date.getFullYear();
    return year < yearConfig.startYear || year > yearConfig.endYear;
});

const generatedUrl = ref(null);
const isGenerating = ref(false);
const isExternalUrl = computed(() => {
    const url = props.baseUrl;
    return url && url[0] == "/";
});

const generateUrl = () => {
    isGenerating.value = true;

    const filters = getFiltersValue();
    const params = [];
    
    if(props.useDivre && filters.divre)
        params.push("divre=" + filters.divre);
    if(props.useWitel && filters.witel)
        params.push("witel=" + filters.witel);
    if(props.useYear && filters.year)
        params.push("year=" + filters.year);
    if(props.useMonth && filters.month)
        params.push("month=" + filters.month);

    let url = props.baseUrl;
    const urlParams = params.join("&");
    if(urlParams)
        url += "?" + urlParams;

    if(isExternalUrl.value)
        url = backendUrl + url;

    setTimeout(() => {
        generatedUrl.value = url;
        isGenerating.value = false;
    }, 300);
};

const runAutoApply = () => {
    if(typeof props.autoApply == "function")
        props.autoApply(getFiltersValue()) && generateUrl();
};

const setupFilter = () => {
    if(props.useMonth && viewStore.filters.month) {
        if(!exportFilter.date)
            exportFilter.date = new Date();
        exportFilter.date.setMonth(viewStore.filters.month - 1);
    }

    if(props.useYear && viewStore.filters.year) {
        if(!exportFilter.date)
            exportFilter.date = new Date();
        exportFilter.date.setFullYear(viewStore.filters.year);
    }

    if(props.useDivre && props.useWitel && currUser.value.level == "divre") {

        exportFilter.divre = currUser.value.locationId;
        listboxDivre.value.setValue(currUser.value.locationId);
        listboxDivre.value.setDisabled(true);

        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(currUser.value.locationId, "gepee"));
        listboxDivre.value.fetch(() => viewStore.getDivre("gepee"));

        if(exportFilter.witel)
            listboxWitel.value.setValue(exportFilter.witel);
        
    } else if(props.useDivre && props.useWitel && currUser.value.level == "witel") {
        
        exportFilter.witel = currUser.value.locationId;
        listboxWitel.value.setValue(currUser.value.locationId);
        listboxWitel.value.setDisabled(true);

        listboxDivre.value.setDisabled(true)

        listboxWitel.value.fetch(
            () => viewStore.getWitel(currUser.value.locationId, "gepee"),
            data => {
                if(data.length < 2)
                    return;

                const { divre_kode } = data[1];
                exportFilter.divre = divre_kode;
                listboxDivre.value.setValue(divre_kode)
                listboxDivre.value.fetch(() => viewStore.getDivre("gepee"));
            }
        );

    } else if(props.useDivre && props.useWitel) {

        listboxDivre.value.fetch(() => viewStore.getDivre());
        if(exportFilter.divre) {
            listboxDivre.value.setValue(exportFilter.divre);
            listboxWitel.value.fetch(() => viewStore.getWitelByDivre(exportFilter.divre));
        }
        if(exportFilter.witel)
            listboxWitel.value.setValue(exportFilter.witel);

    }

    runAutoApply();
};

const colLayoutClass = computed(() => {
    const useDivre = props.useDivre;
    const useWitel = props.useWitel;
    const useYear = props.useYear;
    const useMonth = props.useMonth;
    
    const filterCount = [useDivre, useWitel, (useYear || useMonth)].filter(item => item).length;
    const className = {
        divre: "col-12",
        witel: "col-12",
        date: "col-12"
    };

    if(filterCount === 3) {
        className.divre += " col-md-4 col-xl-6";
        className.witel += " col-md-4 col-xl-3";
        className.date += " col-md-4 col-xl-3";
    } else if(filterCount === 2) {
        className.divre += " col-md-6";
        className.witel += " col-md-6";
        className.date += " col-md-6";
    }

    return className;
});
</script>
<template>
    <Dialog :header="title" v-model:visible="showDialog" modal draggable contentClass="tw-overflow-y-visible"
        :style="{ width: '50vw' }" :breakpoints="{ '960px': '80vw', '641px': '100vw' }"
        @show="setupFilter" @afterHide="$emit('close')">
        <form @submit.prevent="generateUrl">
            <div class="row justify-content-end align-items-end">
                <div v-if="useDivre" :class="colLayoutClass.divre">
                    <div class="mb-2">
                        <label for="inputDivre" :class="{ 'required': requireDivre }">Regional</label>
                        <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                            valueKey="divre_kode" labelKey="divre_name" :useResetItem="!requireDivre"
                            :isRequired="requireDivre" resetTitle="Pilih Semua" @change="onDivreChange" />
                    </div>
                </div>
                <div v-if="useWitel" :class="colLayoutClass.witel">
                    <div class="mb-2">
                        <label for="inputWitel" :class="{ 'required': requireWitel }">Witel</label>
                        <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                            valueKey="witel_kode" labelKey="witel_name" :useResetItem="!requireWitel"
                            :isRequired="requireWitel" resetTitle="Pilih Semua" @change="onWitelChange" />
                    </div>
                </div>
                <div v-if="useMonth || useYear" :class="colLayoutClass.date">
                    <div v-if="useMonth" class="mb-2">
                        <label for="inputMonth" :class="{ 'required': requireMonth }">Bulan</label>
                        <div class="d-grid">
                            <Calendar v-model="exportFilter.date" view="month" dateFormat="M yy" :showButtonBar="!requireMonth" placeholder="Pilih Bulan"
                                :class="{ 'p-invalid': isDateInvalid }" inputId="inputMonth" inputClass="form-control" panelClass="filter-month" />
                        </div>
                    </div>
                    <div v-else class="mb-2">
                        <label for="inputYear" :class="{ 'required': requireYear }">Tahun</label>
                        <div class="d-grid">
                            <Calendar v-model="exportFilter.date" view="year" dateFormat="yy" :showButtonBar="!requireYear" placeholder="Pilih Tahun"
                                :class="{ 'p-invalid': isDateInvalid }" inputId="inputYear" inputClass="form-control" panelClass="filter-month" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 mb-4 d-flex justify-content-end">
                <button type="submit" :disabled="disableSubmit" class="btn btn-primary">Generate Link</button>
            </div>
            <div v-if="generatedUrl || isGenerating" class="d-flex mt-4">
                <div class="card card-body">
                    <a v-if="!isGenerating" :href="generatedUrl" target="_blank" class="stretched-link btn-icon">
                        <span class="tw-truncate tw-w-11/12 tw-opacity-80 d-block">{{ generatedUrl }}</span>
                        <VueFeather type="external-link" size="1.2em" class="ms-1" />
                    </a>
                    <span v-else class="btn-icon">
                        <span class="tw-truncate tw-w-11/12 tw-opacity-80 d-block">{{ generatedUrl }}</span>
                        <VueFeather type="loader" animation="spin" size="1.2em" class="ms-1" />
                    </span>
                </div>
            </div>
        </form>
    </Dialog>
</template>