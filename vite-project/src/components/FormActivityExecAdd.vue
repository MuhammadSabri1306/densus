<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "@components/ui/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const { data, v$ } = useDataForm({
    title: { required },
    description: { required },
    descriptionBefore: { required },
    descriptionAfter: { required },
    evidence: { required }
});

const activityStore = useActivityStore();
const hasSubmitted = ref(false);
const isLoading = ref(false);

const onSave = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
    
    const body = {
        title: data.title,
        description: data.description,
        description_before: data.descriptionBefore,
        description_after: data.descriptionAfter,
        evidence: data.evidence
    };

    isLoading.value = true;
    activityStore.addExecution(scheduleId.value, body, response => {
        isLoading.value = false;
        if(response.success)
            emit("save");
    });
};

const userStore = useUserStore();
const inputerName = computed(() => userStore.name);

const onFileUploaded = event => data.evidence = event.latestUpload;
const onFileRemoved = event => data.evidence = event.latestUpload;
</script>
<template>
    <form @submit.prevent="onSave">
        <div class="mb-4">
            <label for="inputUser">Inputer</label>
            <input type="text" :value="inputerName" id="inputUser" class="form-control" readonly />
        </div>
        <div class="mb-4">
            <label for="inputTitle">Judul Activity <span class="text-danger">*</span></label>
            <input type="text" v-model="data.title" id="inputTitle" :class="{ 'is-invalid': hasSubmitted && v$.title.$invalid }" class="form-control" autofocus />
        </div>
        <div class="mb-4">
            <label for="txtDescription">Deskripsi <span class="text-danger">*</span></label>
            <textarea v-model="data.description" id="inputDescription" :class="{ 'is-invalid': hasSubmitted && v$.description.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionBefore">Before <span class="text-danger">*</span></label>
            <textarea v-model="data.descriptionBefore" id="inputDescriptionBefore" :class="{ 'is-invalid': hasSubmitted && v$.descriptionBefore.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionAfter">After <span class="text-danger">*</span></label>
            <textarea v-model="data.descriptionAfter" id="inputDescriptionAfter" :class="{ 'is-invalid': hasSubmitted && v$.descriptionAfter.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <FileUpload isRequired url="/attachment/activity" label="Evidence"
            accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)"
            @uploaded="onFileUploaded" @removed="onFileRemoved" class="mb-5" />
        <div class="d-flex justify-content-between align-items-end">
            <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Simpan</button>
            <button type="button" @click="$emit('cancel')" class="btn btn-danger">Batalkan</button>
        </div>
    </form>
</template>
<style scoped>

.upload-hidden-bar :deep(.p-fileupload-buttonbar) {
    display: none!important;
}

</style>