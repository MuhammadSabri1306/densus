<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useViewStore } from "@/stores/view";
import { useUserStore } from "@/stores/user";
import { getYearConfig } from "@/configs/filter";
import ListboxFilter from "@/components/ListboxFilter.vue";
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
    divreColClass: { default: "" },
    witelColClass: { default: "" },
    yearColClass: { default: "" },
    monthColClass: { default: "" },
    quarterColClass: { default: "" }
});

const colLayout = computed(() => {
    let divre = "col-12 col-md-4 col-xl-6";
    let witel = "col-md-4 col-xl-3";
    let year = "col-md-4 col-xl-3";
    let month = "col-md-4 col-xl-3";
    let quarter = "col-md-4 col-xl-3";
    
    if(props.useYear && props.useMonth) {
        divre = "col-12 col-md-4 col-xl-5";
        year = "col-4 col-md-2";
        month = "col-4 col-md-2";
    }

    if(props.divreColClass)
        divre = props.divreColClass;
    if(props.witelColClass)
        witel = props.witelColClass;
    if(props.yearColClass)
        year = props.yearColClass;
    if(props.monthColClass)
        month = props.monthColClass;
    if(props.quarterColClass)
        quarter = props.quarterColClass;
    return { divre, witel, year, month, quarter };
});

const viewStore = useViewStore();
const tempFilters = reactive({
    divre: viewStore.filters.divre,
    witel: viewStore.filters.witel,
    date: null,
    quarter: null
});

const isYearEmpty = ref(true);
const filterYear = computed({
    get() {
        if(isYearEmpty.value || !(tempFilters.date instanceof Date))
            return null;
        return tempFilters.date;
    },
    set(newVal) {
        if(newVal) {
            tempFilters.date = newVal;
            isYearEmpty.value = false;
        } else
            isYearEmpty.value = true;
    }
});

const isMonthEmpty = ref(true);
const filterMonth = computed({
    get() {
        if(isMonthEmpty.value || !(tempFilters.date instanceof Date))
            return null;
        return tempFilters.date;
    },
    set(newVal) {
        if(newVal) {
            tempFilters.date = newVal;
            isMonthEmpty.value = false;
        } else
            isMonthEmpty.value = true;
    }
});

const customCalendarYearTitle = computed(() => {
    const date = isYearEmpty.value ? null : tempFilters.date;
    if(!(date instanceof Date))
        return "Pilih Tahun";
    return date.getFullYear();
})

const getFiltersValue = () => {
    const filtersValue = {
        divre: tempFilters.divre,
        witel: tempFilters.witel
    };

    if(props.useMonth || props.useQuarter || props.useYear)
        filtersValue.year = filterYear.value?.getFullYear() || null;
    if(props.useMonth) {
        filtersValue.month = filterMonth.value ? filterMonth.value.getMonth() + 1 : null;
        filtersValue.year = filterMonth.value ? filterMonth.value.getFullYear() : null;
    }
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
    if(val === null) {
        listboxWitel.value.reset();
        tempFilters.witel = null;
    } else
        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val));
};

const onWitelChange = val => tempFilters.witel = val;
const onQuarterChange = val => tempFilters.quarter = val;

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

        let filterDate = tempFilters.date;
        if(!filterDate)
            filterDate = new Date();

        if(viewStore.filters.year) {
            filterDate.setFullYear(viewStore.filters.year);
            isYearEmpty.value = false;
        }

        if(props.useMonth && viewStore.filters.month) {
            filterDate.setMonth(viewStore.filters.month - 1);
            isMonthEmpty.value = false;
        } else if(props.requireMonth) {
            isMonthEmpty.value = false;
        }

        if(props.useQuarter && viewStore.filters.quarter)
            tempFilters.quarter = viewStore.filters.quarter;

        tempFilters.date = filterDate;

    }
    
    if(props.requireYear || props.requireMonth) {
        
        if(props.requireYear)
            isYearEmpty.value = false;
        if(props.requireMonth)
            isMonthEmpty.value = false;
    
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

        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(currUser.value.locationId));
        listboxDivre.value.fetch(() => viewStore.getDivre());

        if(tempFilters.witel)
            listboxWitel.value.setValue(tempFilters.witel);
        
    } else if(currUser.value.level == "witel") {
        
        tempFilters.witel = currUser.value.locationId;
        listboxWitel.value.setValue(currUser.value.locationId);
        listboxWitel.value.setDisabled(true);

        listboxDivre.value.setDisabled(true)

        listboxWitel.value.fetch(
            () => viewStore.getWitel(currUser.value.locationId),
            data => {
                if(data.length < 2)
                    return;

                const { divre_kode } = data[1];
                tempFilters.divre = divre_kode;
                listboxDivre.value.setValue(divre_kode)
                listboxDivre.value.fetch(() => viewStore.getDivre());
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
                            <div :class="colLayout.divre">
                                <div class="mb-2">
                                    <label for="inputDivre" :class="{ 'required': requireDivre }">Regional</label>
                                    <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                                        isRequired valueKey="divre_kode" labelKey="divre_name" :useResetItem="!requireDivre"
                                        resetTitle="Pilih Semua" @change="onDivreChange" />
                                </div>
                            </div>
                            <slot name="filter2" :applyFilter="customFilterApply"></slot>
                            <div :class="colLayout.witel">
                                <div class="mb-2">
                                    <label for="inputWitel" :class="{ 'required': requireWitel }">Witel</label>
                                    <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                                        valueKey="witel_kode" labelKey="witel_name" :useResetItem="!requireWitel"
                                        resetTitle="Pilih Semua" @change="onWitelChange" />
                                </div>
                            </div>
                            <slot name="filter3" :applyFilter="customFilterApply"></slot>
                            <div v-if="useYear" :class="colLayout.year">
                                <div class="mb-2">
                                    <label for="inputYear" :class="{ 'required': requireYear }">Tahun</label>
                                    <div class="d-grid">
                                        <Calendar v-model="filterYear" view="year" dateFormat="yy" :showButtonBar="!requireYear"
                                            placeholder="Pilih Tahun" :class="{ 'p-invalid': requireYear && !filterYear }"
                                            inputId="inputYear" inputClass="form-control text-center" panelClass="tw-min-w-[14rem]" />
                                    </div>
                                </div>
                            </div>
                            <div v-if="useMonth" :class="colLayout.month">
                                <div class="mb-2">
                                    <label for="inputMonth" :class="{ 'required': requireMonth }">Bulan</label>
                                    <div class="d-grid">
                                        <Calendar v-if="!useYear" v-model="filterMonth" view="month" dateFormat="M yy" :showButtonBar="!requireMonth"
                                            placeholder="Pilih Bulan" :class="{ 'p-invalid': requireMonth && !filterMonth }" inputId="inputMonth"
                                            inputClass="form-control" panelClass="filter-month tw-min-w-[14rem]" />
                                        <Calendar v-else v-model="filterMonth" view="month" dateFormat="M" :showButtonBar="!requireMonth"
                                            placeholder="Pilih Bulan" :class="{ 'p-invalid': requireMonth && !filterMonth }" inputId="inputMonth"
                                            inputClass="form-control" panelClass="filter-month hide-year tw-min-w-[14rem]">
                                            <template #header>
                                                <p class="p-datepicker-year p-link tw-cursor-default mx-auto custom-year-title">{{ customCalendarYearTitle }}</p>
                                            </template>
                                        </Calendar>
                                    </div>
                                </div>
                            </div>
                            <div v-if="useQuarter" :class="colLayout.quarter">
                                <div class="mb-2">
                                    <label for="inputQuarter" :class="{ 'required': requireQuarter }">Kuartal</label>
                                    <div class="tw-grid tw-grid-cols-2">
                                        <Calendar v-model="filterYear" view="year" dateFormat="yy" :showButtonBar="!requireQuarter" placeholder="Pilih Tahun"
                                            :class="{ 'p-invalid': (requireYear && !filterYear) || (requireQuarter && !tempFilters.quarter) }"
                                            inputId="inputYear" inputClass="form-control tw-rounded-r-none text-center" panelClass="tw-min-w-[14rem]" />
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