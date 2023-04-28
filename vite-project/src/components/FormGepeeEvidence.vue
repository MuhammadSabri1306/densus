<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useGepeeEvdStore } from "@stores/gepee-evidence";
import { useUserStore } from "@stores/user";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "@components/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);
const props = defineProps({
    initData: { type: Object, default: {} }
});

const route = useRoute();
const idCategory = computed(() => route.params.idCategory);
const idLocation = computed(() => route.params.idLocation);
const idEvidence = computed(() => props.initData.id || null);

const { data, v$ } = useDataForm({
    description: { value: props.initData.deskripsi, required },
    file: { value: props.initData.file }
});

const onFileUploaded = uploadedFile => {
    if(uploadedFile.file_name)
        data.file = uploadedFile.file_name;
};

const gepeeEvdStore = useGepeeEvdStore();
const isLoading = ref(false);
const hasSubmitted = ref(false);

const onSubmit = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;

    const body = { deskripsi: data.description, file: data.file };
    isLoading.value = true;
    if(!idEvidence.value) {

        body.id_location = idLocation.value;
        body.id_category = idCategory.value;
        gepeeEvdStore.create(body, ({ success }) => success && emit('save'));
        
    } else {

        gepeeEvdStore.update(idEvidence.value, body, ({ success }) => success && emit('save'));
    
    }
};
</script>
<template>
    <form @submit.prevent="onSubmit">
        <div class="mb-4">
            <label for="txtDescription" class="required">Deskripsi</label>
            <textarea v-model="data.description" :class="{ 'is-invalid': hasSubmitted && v$.description.$invalid }" class="form-control" id="txtDescription" rows="5"></textarea>
        </div>
        <FileUpload isRequired url="/attachment/gepee-evidence" label="File Evidence" accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)" @uploaded="onFileUploaded" class="mb-5" />
        <div class="d-flex justify-content-between align-items-end">
            <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
            <button type="button" @click="$emit('cancel')" class="btn btn-danger">Batalkan</button>
        </div>
    </form>
</template>