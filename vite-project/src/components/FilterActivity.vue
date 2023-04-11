<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import ListboxFilter from "@components/ListboxFilter.vue";

const emit = defineEmits(["apply"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: true },
    requireWitel: { type: Boolean, default: false },
    requireMonth: { type: Boolean, default: false },
    autoApply: { type: Function, default: () => false }
});

const viewStore = useViewStore();
const tempFilters = reactive({
    divre: viewStore.filters.divre,
    witel: viewStore.filters.witel,
    month: viewStore.filters.month
});

const getFiltersValue = () => {
    const divre = tempFilters.divre;
    const witel = tempFilters.witel;
    const month = tempFilters.month;
    return { divre, witel, month };
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
    const month = tempFilters.month;

    if(props.requireDivre)
        return props.requireDivre && !divre;
    if(props.requireWitel)
        return props.requireWitel && !witel;
    if(props.requireMonth)
        return props.requireMonth && !month;
});

const listboxDivre = ref(null);
const listboxWitel = ref(null);
const listboxMonth = ref(null);

const onDivreChange = val => {
    tempFilters.divre = val;
    listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val, "gepee"));
};

const onWitelChange = val => tempFilters.witel = val;
const onMonthChange = val => tempFilters.month = val;

const runAutoApply = () => {
    if(typeof props.autoApply == "function" && props.autoApply(getFiltersValue())) {
        emit("apply", getFiltersValue());
    }
};

const setupFilter = () => {
    listboxMonth.value.fetch(() => viewStore.monthList);

    if(!tempFilters.month)
        tempFilters.month = new Date().getMonth() + 1;

    listboxMonth.value.setValue(tempFilters.month);

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

// watch(() => viewStore.filters, appliedFilter => {
//     const hasDivreApplied = tempFilters.divre != appliedFilter.divre;
//     const hasWitelApplied = tempFilters.witel != appliedFilter.witel;
//     const hasMonthApplied = tempFilters.month != appliedFilter.month;
//     console.log(hasDivreApplied, hasWitelApplied, hasMonthApplied);
//     if(hasDivreApplied && hasWitelApplied && hasMonthApplied)
//         return;

//     tempFilters.divre = appliedFilter.divre;
//     tempFilters.witel = appliedFilter.witel;
//     tempFilters.month = appliedFilter.month;
//     setupFilter();
// });
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
                        <div class="row justify-content-end align-items-end">
                            <div class="col-12 col-md-4 col-xl-6">
                                <div class="mb-2">
                                    <label for="inputDivre" class="col-form-label">Regional<span v-if="requireDivre" class="text-danger"> *</span></label>
                                    <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                                        isRequired valueKey="divre_kode" labelKey="divre_name" @change="onDivreChange" />
                                </div>
                            </div>
                            <div class="col-md-4 col-xl-3">
                                <div class="mb-2">
                                    <label for="inputWitel" class="col-form-label">Witel<span v-if="requireWitel" class="text-danger"> *</span></label>
                                    <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                                        valueKey="witel_kode" labelKey="witel_name" useResetItem resetTitle="Pilih Semua"
                                        @change="onWitelChange" />
                                </div>
                            </div>
                            <div class="col-md-4 col-xl-3">
                                <div class="mb-2">
                                    <label for="inputMonth" class="col-form-label">Bulan<span v-if="requireMonth" class="text-danger"> *</span></label>
                                    <ListboxFilter ref="listboxMonth" inputId="inputMonth" inputPlaceholder="Pilih Bulan"
                                        valueKey="number" labelKey="name" useResetItem resetTitle="Pilih Semua"
                                        @change="onMonthChange" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" :disabled="disableSubmit" class="btn btn-primary btn-lg">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>