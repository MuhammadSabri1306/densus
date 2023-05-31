<script setup>
import { ref, computed } from "vue";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "@components/ui/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);
const props = defineProps({
    initData: { type: Object, default: {} }
});

const execId = computed(() => props.initData?.id || null);
const { data, v$ } = useDataForm({
    title: { value: props.initData.title, required },
    description: { value: props.initData.description, required },
    descriptionBefore: { value: props.initData.description_before, required },
    descriptionAfter: { value: props.initData.description_after, required },
    evidence: { value: props.initData.evidence, required }
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
    activityStore.updateExecution(execId.value, body, response => {
        isLoading.value = false;
        if(response.success)
            emit("save");
    });
};

const userStore = useUserStore();
const inputerName = computed(() => userStore.name);

const uploadedFile = computed(() => props.initData?.evidence_url || null);
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
            <label for="inputTitle" class="required">Judul Activity</label>
            <input type="text" v-model="data.title" id="inputTitle" :class="{ 'is-invalid': hasSubmitted && v$.title.$invalid }" class="form-control" autofocus />
        </div>
        <div class="mb-4">
            <label for="txtDescription" class="required">Deskripsi</label>
            <textarea v-model="data.description" id="inputDescription" :class="{ 'is-invalid': hasSubmitted && v$.description.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionBefore" class="required">Before</label>
            <textarea v-model="data.descriptionBefore" id="inputDescriptionBefore" :class="{ 'is-invalid': hasSubmitted && v$.descriptionBefore.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="mb-4">
            <label for="txtDescriptionAfter" class="required">After</label>
            <textarea v-model="data.descriptionAfter" id="inputDescriptionAfter" :class="{ 'is-invalid': hasSubmitted && v$.descriptionAfter.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <FileUpload isRequired url="/attachment/activity" :uploadedFile="uploadedFile" label="File Evidence"
            accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)"
            @uploaded="onFileUploaded" @removed="onFileRemoved" class="mb-5" />
        <div class="d-flex justify-content-between align-items-end">
            <button type="button" @click="$emit('cancel')" class="btn btn-danger">Cancel</button>
            <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Save</button>
        </div>
    </form>
</template>