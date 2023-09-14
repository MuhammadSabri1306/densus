<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import { getYearConfig } from "@/configs/filter";
import ListboxFilter from "@components/ListboxFilter.vue";
import Calendar from "primevue/calendar";

const emit = defineEmits(["apply"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: false },
    requireWitel: { type: Boolean, default: false },
    useMonth: { type: Boolean, default: false },
    requireMonth: { type: Boolean, default: false },
    useQuarter: { type: Boolean, default: false },
    requireQuarter: { type: Boolean, default: false },
    useYear: { type: Boolean, default: false },
    requireYear: { type: Boolean, default: false },
    autoApply: { type: Function, default: () => false },
    initData: { type: Object, default: {} },
    rowClass: { default: "row justify-content-end align-items-end" },
    divreColClass: { default: "col-12 col-md-4 col-xl-6" },
    witelColClass: { default: "col-md-4 col-xl-3" },
    monthColClass: { default: "col-md-4 col-xl-3" },
    quarterColClass: { default: "col-md-4 col-xl-3" },
    yearColClass: { default: "col-md-4 col-xl-3" },
});

const viewStore = useViewStore();
const tempFilters = reactive({
    divre: viewStore.filters.divre,
    witel: viewStore.filters.witel,
    date: null,
    quarter: null
});

const getFiltersValue = () => {
    const filtersValue = {
        divre: tempFilters.divre,
        witel: tempFilters.witel
    };

    if(props.useMonth || props.useQuarter || props.useYear)
        filtersValue.year = tempFilters.date ? tempFilters.date.getFullYear() : null;
    if(props.useMonth)
        filtersValue.month = tempFilters.date ? tempFilters.date.getMonth() + 1 : null;
    if(props.useQuarter)
        filtersValue.quarter = tempFilters.quarter;

    return filtersValue;
};

const userStore = useUserStore();
const currUser = computed(() => {
    const level = userStore.level;
    const location = userStore.location;
    const locationId = userStore.locationId;
    return { level, location, locationId };
});

const disableSubmit = computed(() => {
    const divre = tempFilters.divre;
    const witel = tempFilters.witel;
    const date = tempFilters.date;
    const quarter = tempFilters.quarter;

    if(props.requireDivre)
        return props.requireDivre && !divre;
    if(props.requireWitel)
        return props.requireWitel && !witel;
    if(props.requireYear)
        return props.requireYear && !date;
    if(props.requireMonth)
        return props.requireMonth && !date;
    if(props.requireQuarter)
        return props.requireQuarter && !date && !quarter;
});

const listboxDivre = ref(null);
const listboxWitel = ref(null);
const listboxQuarter = ref(null);

const onDivreChange = val => {
    tempFilters.divre = val;
    if(val === null)
        listboxWitel.value.reset();
    else
        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val, "gepee"));
};

const onWitelChange = val => tempFilters.witel = val;
const onQuarterChange = val => tempFilters.quarter = val;

const yearConfig = getYearConfig();
const isDateInvalid = computed(() => {
    const isYearInvalid = props.requireYear && !tempFilters.date;
    const isMonthInvalid = props.requireMonth && !tempFilters.date;
    const isQuarterInvalid = (props.requireQuarter && !tempFilters.date) || (props.requireQuarter && !tempFilters.quarter);
    if(isYearInvalid || isMonthInvalid || isQuarterInvalid)
        return true;
    if(!tempFilters.date || (props.useQuarter && !tempFilters.quarter))
        return false;
    const year = tempFilters.date.getFullYear();
    return year < yearConfig.startYear || year > yearConfig.endYear;
});

const runAutoApply = () => {
    if(typeof props.autoApply == "function" && props.autoApply(getFiltersValue())) {
        emit("apply", getFiltersValue());
    }
};

const quarterList = [
    { number: 1, title: "Q1" },
    { number: 2, title: "Q2" },
    { number: 3, title: "Q3" },
    { number: 4, title: "Q4" }
];

const setupFilter = () => {
    if(props.useMonth || props.useQuarter || props.useYear) {
        if(!tempFilters.date)
            tempFilters.date = new Date();

        if(viewStore.filters.year)
            tempFilters.date.setFullYear(viewStore.filters.year);
        if(props.useMonth && viewStore.filters.month)
            tempFilters.date.setMonth(viewStore.filters.month - 1);
        if(props.useQuarter && viewStore.filters.quarter)
            tempFilters.quarter = viewStore.filters.quarter;
    }

    if(props.useQuarter) {
        listboxQuarter.value.fetch(() => quarterList);
        if(tempFilters.quarter)
            listboxQuarter.value.setValue(tempFilters.quarter);
    }

    if(currUser.value.level == "divre") {

        tempFilters.divre = currUser.value.locationId;
        listboxDivre.value.setValue(currUser.value.locationId);
        listboxDivre.value.setDisabled(true);

        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(currUser.value.locationId, "gepee"));
        listboxDivre.value.fetch(() => viewStore.getDivre("gepee"));

        if(tempFilters.witel)
            listboxWitel.value.setValue(tempFilters.witel);
        
    } else if(currUser.value.level == "witel") {
        
        tempFilters.witel = currUser.value.locationId;
        listboxWitel.value.setValue(currUser.value.locationId);
        listboxWitel.value.setDisabled(true);

        listboxDivre.value.setDisabled(true)

        listboxWitel.value.fetch(
            () => viewStore.getWitel(currUser.value.locationId, "gepee"),
            data => {
                if(data.length < 2)
                    return;

                const { divre_kode } = data[1];
                tempFilters.divre = divre_kode;
                listboxDivre.value.setValue(divre_kode)
                listboxDivre.value.fetch(() => viewStore.getDivre("gepee"));
            }
        );

    } else {

        listboxDivre.value.fetch(() => viewStore.getDivre());
        if(tempFilters.divre) {
            listboxDivre.value.setValue(tempFilters.divre);
            listboxWitel.value.fetch(() => viewStore.getWitelByDivre(tempFilters.divre));
        }
        if(tempFilters.witel)
            listboxWitel.value.setValue(tempFilters.witel);

    }

    runAutoApply();
};

onMounted(() => {
    setupFilter();
});

const customFilterApply = () => emit("apply", getFiltersValue());
</script>
<template>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="card-title">Filter</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="$emit('apply', getFiltersValue())">
                        <div :class="rowClass">
                            <slot name="filter1" :applyFilter="customFilterApply"></slot>
                            <div :class="divreColClass">
                                <div class="mb-2">
                                    <label for="inputDivre" :class="{ 'required': requireDivre }">Regional</label>
                                    <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                                        isRequired valueKey="divre_kode" labelKey="divre_name" :useResetItem="!requireDivre"
                                        resetTitle="Pilih Semua" @change="onDivreChange" />
                                </div>
                            </div>
                            <slot name="filter2" :applyFilter="customFilterApply"></slot>
                            <div :class="witelColClass">
                                <div class="mb-2">
                                    <label for="inputWitel" :class="{ 'required': requireWitel }">Witel</label>
                                    <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                                        valueKey="witel_kode" labelKey="witel_name" :useResetItem="!requireWitel"
                                        resetTitle="Pilih Semua" @change="onWitelChange" />
                                </div>
                            </div>
                            <slot name="filter3" :applyFilter="customFilterApply"></slot>
                            <div v-if="useYear" :class="yearColClass">
                                <div class="mb-2">
                                    <label for="inputYear" :class="{ 'required': requireYear }">Tahun</label>
                                    <div class="d-grid">
                                        <Calendar v-model="tempFilters.date" view="year" dateFormat="yy" :showButtonBar="!requireYear" placeholder="Pilih Tahun"
                                            :class="{ 'p-invalid': isDateInvalid }" inputId="inputYear" inputClass="form-control text-center" panelClass="tw-min-w-[14rem]" />
                                    </div>
                                </div>
                            </div>
                            <div v-if="useMonth" :class="monthColClass">
                                <div class="mb-2">
                                    <label for="inputMonth" :class="{ 'required': requireMonth }">Bulan</label>
                                    <div class="d-grid">
                                        <Calendar v-model="tempFilters.date" view="month" :dateFormat="useYear ? 'M' : 'M yy'" :showButtonBar="!requireMonth" placeholder="Pilih Bulan"
                                            :class="{ 'p-invalid': isDateInvalid }" inputId="inputMonth" inputClass="form-control" panelClass="filter-month" />
                                    </div>
                                </div>
                            </div>
                            <div v-if="useQuarter" :class="quarterColClass">
                                <div class="mb-2">
                                    <label for="inputQuarter" :class="{ 'required': requireQuarter }">Kuartal</label>
                                    <div class="tw-grid tw-grid-cols-2">
                                        <Calendar v-model="tempFilters.date" view="year" dateFormat="yy" :showButtonBar="!requireQuarter" placeholder="Pilih Tahun"
                                            :class="{ 'p-invalid': isDateInvalid }" inputId="inputYear" inputClass="form-control tw-rounded-r-none text-center" panelClass="tw-min-w-[14rem]" />
                                        <ListboxFilter ref="listboxQuarter" inputId="inputQuarter" inputPlaceholder="Pilih Kuartal" class="listbox-quarter"
                                            valueKey="number" labelKey="title" :useSearchField="false" :useResetItem="!requireQuarter" resetTitle="Pilih Semua"
                                            @change="onQuarterChange" />
                                    </div>
                                </div>
                            </div>
                            <slot name="filter4" :applyFilter="customFilterApply"></slot>
                        </div>
                        <div class="mt-3 d-flex align-items-end">
                            <slot name="action" :applyFilter="customFilterApply"></slot>
                            <button type="submit" :disabled="disableSubmit" class="ms-auto btn btn-primary btn-lg">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>

.listbox-quarter :deep(.form-control) {
    @apply tw-rounded-l-none tw-border-l-0 tw-text-center;
}

.listbox-quarter :deep(.listbox-wrapper) {
    @apply tw-min-w-[8rem] tw-right-0;
}

</style>