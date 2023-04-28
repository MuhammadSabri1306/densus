<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { apiEndpoint, apiHeaders } from "@/configs/base";
import FileUpload from "primevue/fileupload";

const emit = defineEmits(["uploaded"]);
const props = defineProps({
    isRequired: Boolean,
    url: { type: String, required: true },
    name: { type: String, default: "file" },
    label: String,
    accept: String,
    acceptText: String,
});

const uploadUrl = computed(() => apiEndpoint + props.url);

const isValid = ref(true);
const setValidValue = val => isValid.value = val;
defineExpose({ setValidValue });

const onSelect = () => {
    isValid.value = true;
};

const userStore = useUserStore();
const userToken = computed(() => userStore.token);
const onBeforeSend = ({ xhr, formData }) => {
    for(let key in apiHeaders) {
        xhr.setRequestHeader(key, apiHeaders[key]);
    }
    xhr.setRequestHeader("Authorization", "Bearer " + userToken.value);
    console.log(formData.get(props.name));
};

const viewStore = useViewStore();
const getResponseData = xhr => {
    try {
        return JSON.parse(xhr.responseText);
    } catch(err) {
        console.warn(xhr.responseText, err);
        return null;
    }
};

const onUploaded = ({ xhr }) => {
    const responseData = getResponseData(xhr);
    if(!responseData.success)
        console.warn(responseData);
    if(!responseData.success && responseData.message)
        viewStore.showToast("Upload file", responseData.message, false);
    if(responseData.success && responseData.uploadedFile)
        emit("uploaded", responseData.uploadedFile);
};

const onError = ({ xhr }) => {
    isValid.value = false;

    const responseData = getResponseData(xhr);
    if(responseData.message)
        viewStore.showToast("Upload file", responseData.message, false);
};

const onFileRemove = ({ file }) => {
    console.log(file);
};
</script>
<template>
    <div>
        <label for="inputFile" :class="{ 'required': isRequired }">{{ label }}</label>
        <div :class="{ 'is-invalid': !isValid }" class="file-uploader">
            <FileUpload :url="uploadUrl" :name="name" auto :fileLimit="1" :accept="accept"
                chooseLabel="Pilih File" uploadLabel="Upload" cancelLabel="Batal"
                @select="onSelect" @beforeSend="onBeforeSend" @error="onError" @remove="onFileRemove">
                <template #empty>
                    <p>Drag dan drop file anda disini.</p>
                </template>
            </FileUpload>
        </div>
        <p class="text-muted ms-4"><small><b>{{ acceptText }}</b></small></p>
    </div>
</template>
<style scoped>

.file-uploader :deep(.p-fileupload-buttonbar) button {
    background-color: #14B8A6;
}

.file-uploader.is-invalid {
    @apply tw-border tw-border-danger;
}

</style>