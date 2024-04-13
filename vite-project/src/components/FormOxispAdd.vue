<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useOxispStore } from "@/stores/oxisp";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "@/components/ui/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);
const props = defineProps({
    initData: { type: Object, default: {} }
});

const route = useRoute();
const year = computed(() => route.params.year);
const month = computed(() => route.params.month);
const idLocation = computed(() => route.params.idLocation);

const { data, v$ } = useDataForm({
    title: { required },
    descr: { required },
    descr_before: { required },
    descr_after: { required },
    evidence: { required }
});

const oxispStore = useOxispStore();
const hasSubmitted = ref(false);
const isLoading = ref(false);

const onSave = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        id_sto: idLocation.value,
        title: data.title,
        descr: data.descr,
        descr_before: data.descr_before,
        descr_after: data.descr_after,
        evidence: data.evidence
    };

    isLoading.value = true;
    oxispStore.create(year.value, month.value, body, response => {
        isLoading.value = false;
        if(response.success)
            emit("save");
    });
};

const onFileUploaded = event => data.evidence = event.latestUpload;
const onFileRemoved = event => data.evidence = event.latestUpload;
</script>
<template>
    <form @submit.prevent="onSave">
        <div class="mb-4">
            <label for="inputTitle" class="required">Judul Activity</label>
            <input type="text" v-model="data.title" id="inputTitle" :class="{ 'is-invalid': hasSubmitted && v$.title.$invalid }" class="form-control" autofocus />
        </div>
        <div class="mb-4">
            <label for="txtDescription" class="required">Deskripsi</label>
            <textarea v-model="data.descr" id="inputDescription" :class="{ 'is-invalid': hasSubmitted && v$.descr.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionBefore" class="required">Before</label>
            <textarea v-model="data.descr_before" id="inputDescriptionBefore" :class="{ 'is-invalid': hasSubmitted && v$.descr_before.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionAfter" class="required">After</label>
            <textarea v-model="data.descr_after" id="inputDescriptionAfter" :class="{ 'is-invalid': hasSubmitted && v$.descr_after.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <FileUpload isRequired url="/attachment/oxisp" label="Evidence"
            accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)"
            @uploaded="onFileUploaded" @removed="onFileRemoved" class="mb-5" />
        <div class="d-flex justify-content-between align-items-end">
            <button type="button" @click="$emit('cancel')" class="btn btn-danger">Batalkan</button>
            <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
        </div>
    </form>
</template>