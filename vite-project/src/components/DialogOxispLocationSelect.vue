<script setup>
import { computed, ref } from "vue";
import { useViewStore } from "@/stores/view";
import Dialog from "primevue/dialog";
import InputGroupLocation from "@/components/InputGroupLocation.vue";
import ListboxFilter from "@/components/ListboxFilter.vue";

const emit = defineEmits(["select", "close"]);
const showDialog = ref(true);

const idLocation = ref(null);
const listboxSelectSto = ref(null);
const onGepeeLocChange = id => idLocation.value = id;

const inputLocation = ref(null);
const onLocationChange = (loc) => {
    if(!listboxSelectSto.value)
        return;
    const divre = loc.divre_kode;
    const witel = loc.witel_kode;
    listboxSelectSto.value.setDisabled(false);
    listboxSelectSto.value.fetch(
        () => viewStore.getSto({ divre, witel }, "sto_master"),
        list => {
            const index = list.findIndex(item => item.id_sto == idLocation.value);
            if(index >= 0)
                return;
            listboxSelectSto.value.setValue(null);
            idLocation.value = null;
        }
    );
};

const viewStore = useViewStore();

const disableBtnNext = computed(() => idLocation.value ? false : true);
const onStoSubmit = () => {
    if(!idLocation.value)
        return;
    emit('select', idLocation.value);
    showDialog.value = false;
};

const dialogProps = {
    class: "dialog-basic",
    modal: true,
    maximizable: true,
    draggable: true
};
</script>
<template>
    <Dialog header="Pilih Lokasi OXISP" v-model:visible="showDialog"  v-bind="dialogProps"
        contentClass="tw-overflow-y-visible" @afterHide="$emit('close')">
        <div class="p-4">
            <form @submit.prevent="onStoSubmit">
                <InputGroupLocation ref="inputLocation" locationType="sto_master" requireDivre requireWitel
                    position="bottom" @change="onLocationChange" class="mb-4" />
                <div class="form-group mb-5">
                    <label id="inputSto" class="required">Pilih Lokasi</label>
                    <ListboxFilter ref="listboxSelectSto" inputId="inputSto" inputPlaceholder="Pilih Lokasi STO"
                        isRequired valueKey="id_sto" labelKey="sto_name" @change="onGepeeLocChange" />
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="submit" :disabled="disableBtnNext" class="btn btn-lg btn-primary">Lanjutkan</button>
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>