<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import http from "@helpers/http-common";
import { apiEndpoint } from "@/configs/base";
import { fromBytes, fromMb } from "@helpers/byte-unit";
import FileUpload from "primevue/fileupload";

const emit = defineEmits(["uploaded", "removed"]);
const props = defineProps({
    isRequired: Boolean,
    url: { type: String, required: true },
    name: { type: String, default: "file" },
    maxSize: { type: Number, default: 4096 }, // on MB,
    fileLimit: { type: Number, default: 1 },
    label: String,
    accept: String,
    acceptText: String,
    initUploadedFiles: { type: Array }
});

const uploadUrl = computed(() => apiEndpoint + props.url);
const maxFileSize = computed(() => fromMb(props.maxSize).toBytes());
const formatFileSize = (bytes) => fromBytes(bytes).toAutoText();

const uploadedFiles = ref([]);
const hasUploaded = ref(false);
const isLoading = ref(false);
const isInvalid = computed(() => props.isRequired && hasUploaded.value && uploadedFiles.value.length < 1);

const userStore = useUserStore();
const viewStore = useViewStore();

const getRequestConfig = () => {
    const config = userStore.axiosAuthConfig;
    config.headers["Content-Type"] = "multipart/form-data";
    return config;
};

const uploadFile = async (file) => {
    const formData = new FormData();
    formData.append(props.name, file);
    isLoading.value = true;

    try {
        const response = await http.post(props.url, formData, getRequestConfig());
        if(response.data?.uploadedFile) {

            const fileList = uploadedFiles.value;
            fileList.push(response.data.uploadedFile);
            uploadedFiles.value = fileList;
            isLoading.value = false;

            emit("uploaded", {
                isInvalid: isInvalid.value,
                files: uploadedFiles.value.map(item => item.file_name),
                latestUpload: response.data.uploadedFile.file_name
            });
        
        }
    } catch(err) {

        console.error(err);
        isLoading.value = false;
        if(err.response && err.response.data)
            viewStore.showToast("Upload file", err.response.data?.message, false);

    }
};

const fileUploader = async ({ files }) => {
    for(let i=0; i<files.length; i++) {
        await uploadFile(files[i]);
    }
};

const removeFile = async (index) => {
    const filename = uploadedFiles.value[index].file_name;
    const url = props.url.slice(-1) == "/" ? props.url + filename
        : props.url + "/" + filename;
    isLoading.value = true;

    try {
        const response = await http.delete(url, getRequestConfig());
        if(response.data.success) {

            const fileList = uploadedFiles.value.filter((i, itemIndex) => itemIndex !== index);
            uploadedFiles.value = fileList;
            isLoading.value = false;

            emit("removed", {
                isInvalid: isInvalid.value,
                files: uploadedFiles.value.map(item => item.file_name),
                latestUpload: fileList.length < 1 ? null : fileList[fileList.length - 1].file_name,
                latestRemove: filename
            });
        
        }
    } catch(err) {
        console.error(err);
        isLoading.value = false;
        viewStore.showToast("Hapus file", `Gagal menghapus file ${ filename }.`, false);
    }
};

const onClearUploadedFile = async () => {
    for(let i=0; i<uploadedFiles.value.length; i++) {
        await removeFile(i);
    }
};

const disableBtnChoose = computed(() => uploadedFiles.value.length >= props.fileLimit);
const disableBtnReset = computed(() => uploadedFiles.value.length < 1);
</script>
<template>
    <div>
        <label for="inputFile" :class="{ 'required': isRequired }">{{ label }}</label>
        <div :class="{ 'is-invalid': isInvalid }" class="file-uploader">
            <FileUpload :url="uploadUrl" :name="name" auto :fileLimit="fileLimit" :maxFileSize="maxFileSize"
                withCredentials :accept="accept" customUpload @uploader="fileUploader">
                <template #header="{ chooseCallback }">
                    <div class="w-100">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <button type="button" @click="chooseCallback()" :disabled="disableBtnChoose" class="btn btn-info btn-icon">
                                    <VueFeather type="plus" size="1.2em" class="middle" />
                                    <span>Pilih File</span>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" @click="onClearUploadedFile" :disabled="disableBtnReset" class="btn btn-secondary btn-icon">
                                    <VueFeather type="x" size="1.2em" class="middle" />
                                    <span>Reset</span>
                                </button>
                            </div>
                            <div v-if="isLoading" class="col-auto ms-auto">
                                <VueFeather type="loader" size="2rem" animation="spin" class="middle text-muted" />
                            </div>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div v-if="uploadedFiles.length > 0">
                        <table class="table">
                            <tr v-for="(file, index) of uploadedFiles">
                                <td class="middle">
                                    <img role="presentation" :alt="file.file_name" :src="file.uploaded_url" width="50" class="img-fluid" />
                                </td>
                                <td class="middle font-semibold">
                                    {{ file.file_name }}
                                </td>
                                <td class="middle">
                                    {{ formatFileSize(file.file_size) }}
                                </td>
                                <td class="middle">
                                    <button type="button" @click="removeFile(index)">
                                        <VueFeather type="x" />
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </template>
                <template #empty>
                    <p v-if="uploadedFiles.length < 1">Drag dan drop file anda disini.</p>
                </template>
            </FileUpload>
        </div>
        <p class="text-muted ms-4"><small>Max. <b>{{ maxSize }} MB</b>, <b>{{ acceptText }}</b></small></p>
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