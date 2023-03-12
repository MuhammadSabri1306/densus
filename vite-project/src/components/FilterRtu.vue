<script setup>
import { ref, computed, onMounted } from "vue";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import ListboxFilter from "@components/ListboxFilter.vue";

const emit = defineEmits(["apply"]);
const props = defineProps({
    requireDivre: { type: Boolean, default: true },
    requireWitel: { type: Boolean, default: true }
});

const userStore = useUserStore();
const currUser = computed(() => {
    const level = userStore.level;
    const location = userStore.location;
    const locationId = userStore.locationId;
    return { level, location, locationId };
});

const viewStore = useViewStore();
const disableSubmit = computed(() => {
    const divre = viewStore.filters.divre;
    const witel = viewStore.filters.witel;

    if(props.requireDivre)
        return props.requireDivre && !divre;
    if(props.requireWitel)
        return props.requireWitel && !witel;
});

const listboxDivre = ref(null);
const listboxWitel = ref(null);

const onDivreChange = val => {
    viewStore.setFilter({ divre: val });
    listboxWitel.value.fetch(() => viewStore.getWitelByDivre(val));
};

const onWitelChange = val => viewStore.setFilter({ witel: val });

onMounted(() => {
    if(currUser.value.level == "divre") {

        viewStore.setFilter({ divre: currUser.value.locationId });
        listboxDivre.value.setValue(currUser.value.locationId);
        listboxDivre.value.setDisabled(true);

        listboxWitel.value.fetch(() => viewStore.getWitelByDivre(currUser.value.locationId));
        listboxDivre.value.fetch(() => viewStore.getDivre());

        if(viewStore.filters.witel)
            listboxWitel.value.setValue(viewStore.filters.witel);
        
    } else if(currUser.value.level == "witel") {
        
        viewStore.setFilter({ witel: currUser.value.locationId });
        listboxWitel.value.setValue(currUser.value.locationId);
        listboxWitel.value.setDisabled(true);

        listboxDivre.value.setDisabled(true)

        listboxWitel.value.fetch(
            () => viewStore.getWitel(currUser.value.locationId),
            data => {
                if(data.length < 2)
                    return;

                const { divre_kode } = data[1];
                viewStore.setFilter({ divre: divre_kode });
                listboxDivre.value.setValue(divre_kode)
                listboxDivre.value.fetch(
                    () => viewStore.getDivre(),
                    () => emit("apply")
                );
            }
        );

    } else {

        listboxDivre.value.fetch(() => viewStore.getDivre());
        if(viewStore.filters.divre)
            listboxDivre.value.setValue(viewStore.filters.divre);
        if(viewStore.filters.witel)
            listboxWitel.value.setValue(viewStore.filters.witel);

    }
});
</script>
<template>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="card-title">Filter</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="$emit('apply')">
                        <div class="row justify-content-end align-items-end">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="inputDivre" class="col-form-label">Regional<span v-if="requireDivre" class="text-danger"> *</span></label>
                                    <ListboxFilter ref="listboxDivre" inputId="inputDivre" inputPlaceholder="Pilih Divre"
                                        isRequired valueKey="divre_kode" labelKey="divre_name" @change="onDivreChange" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="inputWitel" class="col-form-label">Witel<span v-if="requireWitel" class="text-danger"> *</span></label>
                                    <ListboxFilter ref="listboxWitel" inputId="inputWitel" inputPlaceholder="Pilih Witel"
                                        valueKey="witel_kode" labelKey="witel_name" useResetItem resetTitle="Pilih Semua"
                                        @change="onWitelChange" />
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