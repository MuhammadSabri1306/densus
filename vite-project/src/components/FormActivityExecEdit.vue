<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useActivityStore } from "@stores/activity";
import { useUserStore } from "@stores/user";
import { required } from "@vuelidate/validators";
import { useDataForm } from "@/helpers/data-form";

const emit = defineEmits(["save", "cancel"]);
const props = defineProps({
    initData: { type: Object, default: {} }
});

const route = useRoute();
const scheduleId = computed(() => route.params.scheduleId);

const { data, v$ } = useDataForm({
    title: { value: props.initData.title, required },
    description: { value: props.initData.description, required },
    descriptionBefore: { value: props.initData.description_before, required },
    descriptionAfter: { value: props.initData.description_after, required }
});

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

    isLoading.value = true;
    activityStore.updateExecution(props.initData.id, formData, response => {
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
        <div class="mb-5">
            <label for="txtDescriptionAfter">After <span class="text-danger">*</span></label>
            <textarea v-model="data.descriptionAfter" id="inputDescriptionAfter" :class="{ 'is-invalid': hasSubmitted && v$.descriptionAfter.$invalid }" rows="5" class="form-control"></textarea>
        </div>
        <div class="d-flex justify-content-between align-items-end">
            <button type="submit" :class="{ 'btn-loading': isLoading }" class="btn btn-lg btn-primary">Save</button>
            <button type="button" @click="$emit('cancel')" class="btn btn-danger">Cancel</button>
        </div>
    </form>
</template>