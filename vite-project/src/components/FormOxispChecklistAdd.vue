<script setup>
import { ref, computed } from "vue";
import { useOxispCheckStore } from "@stores/oxisp-check";
import { watchLoading } from "@stores/view";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form2";
import FileUpload from "@components/ui/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);
const props = defineProps({
    currCheck: { type: Object, default: {} }
});

const { data, v$, hasSubmitted, getInvalidClass } = useDataForm({
    is_room_exists: { value: true },
    is_ok: { value: true },
    note: { required },
    evidence: { required }
});

const oxispCheckStore = useOxispCheckStore();
const isLoading = ref(false);
watchLoading(isLoading);

const onSave = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(data.is_room_exists && !isValid)
        return;
    
    const body = {
        is_room_exists: data.is_room_exists,
        id_location: props.currCheck?.location.id,
        id_room: props.currCheck?.room.id,
        check_category_code: props.currCheck?.category.code,
        is_ok: data.is_ok,
        note: data.note,
        evidence: data.evidence,
        year: props.currCheck?.year,
        month: props.currCheck?.month
    };

    isLoading.value = true;
    oxispCheckStore.create(body, response => {
        isLoading.value = false;
        if(response.success)
            emit("save");
    });
};

const onEvdUploaded = event => data.evidence = event.latestUpload;
const onEvdRemoved = event => data.evidence = event.latestUpload;
const isInputDisabled = computed(() => !data.is_room_exists);
</script>
<template>
    <form @submit.prevent="onSave">
        <div class="mb-4">
            <div><label class="mb-0">Ketersediaan Ruangan</label></div>
            <div class="px-4 form-group m-t-15 m-checkbox-inline mb-0 custom-radio-ml">
                <div class="radio radio-primary">
                    <input type="radio" v-model="data.is_room_exists" :value="true" id="radioIsRoomExists1">
                    <label class="mb-0" for="radioIsRoomExists1">Sudah Ada</label>
                </div>
                <div class="radio radio-primary">
                    <input type="radio" v-model="data.is_room_exists" :value="false" id="radioIsRoomExists2">
                    <label class="mb-0" for="radioIsRoomExists2">Belum Ada</label>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div><label class="mb-0">Kondisi</label></div>
            <div class="px-4 form-group m-t-15 m-checkbox-inline mb-0 custom-radio-ml">
                <div class="radio radio-primary">
                    <input type="radio" v-model="data.is_ok" :value="true" :disabled="isInputDisabled" id="radioIsOk1">
                    <label class="mb-0" for="radioIsOk1">OK</label>
                </div>
                <div class="radio radio-primary">
                    <input type="radio" v-model="data.is_ok" :value="false" :disabled="isInputDisabled" id="radioIsOk2">
                    <label class="mb-0" for="radioIsOk2">Not OK</label>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label for="txtNote" class="required">Catatan</label>
            <textarea v-model="data.note" id="txtNote" :class="data.is_room_exists ? getInvalidClass('note', 'is-invalid') : null"
                :disabled="isInputDisabled" rows="5" class="form-control" autofocus></textarea>
        </div>
        <FileUpload isRequired :isDisabled="isInputDisabled || isLoading" url="/attachment/oxisp-check" label="Evidence"
            accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)"
            @uploaded="onEvdUploaded" @removed="onEvdRemoved" class="mb-5" />
        <div class="d-flex justify-content-between align-items-end">
            <button type="button" @click="$emit('cancel')" :disabled="isLoading"
                class="btn btn-danger">Batalkan</button>
            <button type="submit" :class="{ 'btn-loading': isLoading }" :disabled="isLoading"
                class="btn btn-lg btn-primary">Simpan</button>
        </div>
    </form>
</template>