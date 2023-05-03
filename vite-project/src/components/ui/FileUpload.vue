<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import http from "@helpers/http-common";
import { apiEndpoint } from "@/configs/base";
import { fromBytes, fromMb } from "@helpers/byte-unit";
import { getFileName, getFileExt, isFileImg, getFileRawName } from "@helpers/file";
import FileUpload from "primevue/fileupload";
import { DocumentIcon } from "@heroicons/vue/24/solid";

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
    initUploadedFiles: { type: Array },
    hasSubmitted: { type: Boolean, default: false },
    uploadedFile: [String, Array]
});

const uploadUrl = computed(() => apiEndpoint + props.url);
const maxFileSize = computed(() => fromMb(props.maxSize).toBytes());
const getFileSize = bytes => bytes ? fromBytes(bytes).toAutoText() : null;

const createInitItem = filePath => {
    const fileName = getFileName(filePath);
    const client_name = fileName;
    const file_ext = getFileExt(fileName);
    const file_name = fileName;
    const is_image = isFileImg(fileName);
    const raw_name = getFileRawName(fileName);
    const uploaded_url = filePath;

    return { client_name, file_ext, file_name, is_image, raw_name, uploaded_url };
};

let initFileList = [];
if(props.uploadedFile && typeof props.uploadedFile == "string") {
    initFileList = [ createInitItem(props.uploadedFile) ];
} else if(props.uploadedFile && Array.isArray(props.uploadedFile)) {
    initFileList = props.uploadedFile.map(item => createInitItem(item));
}
const uploadedFiles = ref(initFileList);

const hasUploaded = ref(false);
const hasSubmitted = computed(() => props.hasSubmitted);
const isLoading = ref(false);
const isInvalid = computed(() => props.isRequired && (hasUploaded.value || hasSubmitted.value) && uploadedFiles.value.length < 1);

const userStore = useUserStore();
const viewStore = useViewStore();
const requestConfig = computed(() => userStore.axiosUploadConfig);

const uploadFile = async (file) => {
    const formData = new FormData();
    formData.append(props.name, file);
    isLoading.value = true;

    try {
        const response = await http.post(props.url, formData, requestConfig.value);
        if(response.data?.uploadedFile) {

            const { client_name, file_ext, file_name, is_image, raw_name, uploaded_url } = response.data.uploadedFile;
            const fileList = uploadedFiles.value;
            fileList.push({ client_name, file_ext, file_name, is_image, raw_name, uploaded_url });
            
            uploadedFiles.value = fileList;
            isLoading.value = false;

            emit("uploaded", {
                isInvalid: isInvalid.value,
                files: uploadedFiles.value.map(item => item.file_name),
                latestUpload: response.data.uploadedFile.file_name
            });
        
        }
        return false;
    } catch(err) {

        console.error(err);
        isLoading.value = false;
        if(err.response && err.response.data)
            viewStore.showToast("Upload file", err.response.data?.message, false);

    }
};

const onFileSelect = async ({ files }) => {
    for(let i=0; i<files.length; i++) {
        await uploadFile(files[i]);
    }
    files = [];
};

const removeFile = async (index) => {
    const filename = uploadedFiles.value[index].file_name;
    const url = props.url.slice(-1) == "/" ? props.url + filename
        : props.url + "/" + filename;
    isLoading.value = true;

    try {
        const response = await http.delete(url, requestConfig.value);
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

const clearFiles = async () => {
    for(let i=0; i<uploadedFiles.value.length; i++) {
        await removeFile(i);
    }
};

const disableUpload = computed(() => uploadedFiles.value.length >= props.fileLimit);
const disableClear = computed(() => uploadedFiles.value.length < 1);
</script>
<template>
    <div>
        <label for="inputFile" :class="{ 'required': isRequired }">{{ label }}</label>
        <div :class="{ 'is-invalid': isInvalid }" class="file-uploader">
            <FileUpload :url="uploadUrl" :name="name" auto :disabled="disableUpload" :maxFileSize="maxFileSize"
                withCredentials :accept="accept" customUpload @select="onFileSelect">
                <template #header="{ chooseCallback, clearCallback }">
                    <div class="w-100">
                        <div class="row align-items-center g-4">
                            <div class="col-auto">
                                <button type="button" @click="chooseCallback()" :disabled="disableUpload" class="btn btn-info btn-icon">
                                    <VueFeather type="plus" size="1.2em" class="middle" />
                                    <span>Pilih File</span>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" @click="clearFiles() && clearCallback()" :disabled="disableClear" class="btn btn-secondary btn-icon">
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
                <template #content="{ removeFileCallback }">
                    <div v-if="uploadedFiles.length > 0">
                        <table class="table">
                            <tr v-for="(file, index) of uploadedFiles">
                                <td class="middle">
                                    <img v-if="file.is_image" role="presentation" :alt="file.file_name" :src="file.uploaded_url" width="50" class="tw-h-auto" />
                                    <DocumentIcon v-else class="tw-w-8 tw-h-8 text-muted" />
                                </td>
                                <td class="middle font-semibold">
                                    {{ file.file_name }}
                                </td>
                                <td class="middle">
                                    {{ getFileSize(file.file_size) }}
                                </td>
                                <td class="middle">
                                    <button type="button" @click="removeFile(index) && removeFileCallback(index)">
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