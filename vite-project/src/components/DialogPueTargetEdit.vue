<script setup>
import { ref, computed } from "vue";
import { usePueTargetStore } from "@stores/pue-target";
import { useViewStore } from "@stores/view";
import Dialog from "primevue/dialog";
import { required, integer } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
    initData: { type: Object, required: true }
});

const showDialog = ref(true);
const targetId = computed(() => props.initData?.id || null);

const { data, v$ } = useDataForm({
    target: { value: props.initData.value, required, integer }
});

const pueTargetStore = usePueTargetStore();
const viewStore = useViewStore();

const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        target: data.target
    };
    
    isLoading.value = true;
    pueTargetStore.update(targetId.value, body, response => {
        if(response.success) {
            viewStore.showToast("Target PUE", "Berhasil memperbarui item.", true);
            emit("save");
            showDialog.value = false;
        }
        isLoading.value = false;
    });
};
</script>
<template>
    <Dialog header="Form Update Target PUE" v-model:visible="showDialog"
        maximizable modal draggable @afterHide="$emit('close')" contentClass="tw-overflow-y-visible"
        :style="{ width: '50vw' }" :breakpoints="{ '960px': '80vw', '641px': '100vw' }">
        <div class="p-4">
            <form @submit.prevent="onSubmit">
                <div class="mb-4">
                    <table class="table">
                        <tr>
                            <td>Regional</td>
                            <td>:</td>
                            <th>{{ initData.divre_name }}</th>
                        </tr>
                        <tr>
                            <td>Witel</td>
                            <td>:</td>
                            <th>{{ initData.witel_name }}</th>
                        </tr>
                    </table>
                </div>
                <div class="row px-4 mb-5">
                    <div class="col-md-6">
                        <div class="form-group mb-5">
                            <label for="inputTarget" class="required">Target Lokasi PUE</label>
                            <input type="text" v-model="v$.target.$model" :class="{ 'is-invalid': hasSubmitted && v$.target.$invalid }"
                                class="form-control" id="inputTarget" placeholder="Cth. 5">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
                    <button type="button" @click="showDialog = false" class="btn btn-danger">Batalkan</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>