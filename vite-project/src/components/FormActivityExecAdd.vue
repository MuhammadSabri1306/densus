<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";
import FileUpload from "primevue/fileupload";
// import FileUpload from "@components/FileUpload.vue";

const emit = defineEmits(["save", "cancel"]);

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const { data, v$ } = useDataForm({
    title: { required },
    description: { required },
    descriptionBefore: { required },
    descriptionAfter: { required },
    evidence: { value: null }
});

const hideUploadBtnBar = ref(false);
const onUpload = event => {
    const file = event.files[event.files.length - 1];
    console.log(file, event);
    if(file) {
        data.evidence = file;
        hideUploadBtnBar.value = true;
    }
};

const onUploadRemove = () => {
    data.evidence = null;
};

const activityStore = useActivityStore();
const hasSubmitted = ref(false);
const isLoading = ref(false);

const onSave = async () => {
    hasSubmitted.value = true;
    const isValid = await v$.value.$validate();
    if(!isValid)
        return;
        
    const formData = new FormData();
    formData.append("title", data.title);
    formData.append("description", data.description);
    formData.append("description_before", data.descriptionBefore);
    formData.append("description_after", data.descriptionAfter);
    formData.append("evidence", data.evidence);

    isLoading.value = true;
    activityStore.addExecution(scheduleId.value, formData, response => {
        isLoading.value = false;
        if(response.success)
            emit("save");
    });
};

const userStore = useUserStore();
const inputerName = computed(() => userStore.name);
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
        <div class="mb-5">
            <label for="inputEvidence">Evidence <span class="text-danger">*</span></label>
            <FileUpload :customUpload="true" :fileLimit="1" @select="onUpload($event)" @clear="onUploadRemove" @remove="onUploadRemove" accept=".jpg, .jpeg, .png, .pdf" :showUploadButton="false" :showCancelButton="false" uploadLabel="inputEvidence">
                <template #empty>
                    <p>Drag dan drop file anda disini.</p>
                </template>
            </FileUpload>
            <p class="text-muted ms-4"><small><b>(*.jpg, *.jpeg, *.png, *.pdf)</b></small></p>
        </div>
        <!-- <FileUpload class="mb-5" isRequired label="Evidence" accept=".jpg, .jpeg, .png, .pdf" acceptText="(*.jpg, *.jpeg, *.png, *.pdf)" /> -->
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