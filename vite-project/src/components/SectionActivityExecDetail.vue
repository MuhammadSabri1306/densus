<script setup>
import { ref, computed } from "vue";
import Image from "primevue/image";
defineEmits(["close"]);

const props = defineProps({
    data: { type: Object, default: {} },
    showCloseButton: { type: Boolean, default: true }
});

const evidenceExt = computed(() => {
    const nameArr = (props.data.evidence || "").split(".");
    return nameArr[nameArr.length - 1];
});

const isEvidenceImg = computed(() => evidenceExt.value == "jpg" || evidenceExt.value == "jpeg" || evidenceExt.value == "png");
</script>
<template>
    <section>
        <div>
            <table class="table text-muted">
                <tbody>
                    <tr>
                        <th>Judul Activity</th>
                        <td>{{ data.title }}</td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ data.description }}</td>
                    </tr>
                    <tr>
                        <th>Before</th>
                        <td>{{ data.description_before }}</td>
                    </tr>
                    <tr>
                        <th>After</th>
                        <td>{{ data.description_after }}</td>
                    </tr>
                    <tr>
                        <th>Evidence</th>
                        <td>
                            <div v-if="isEvidenceImg" class="position-relative">
                                <span>{{ data.evidence }}</span>
                                <Image :src="data.evidence_url" alt="gambar evidence" preview class="evidence-img" />
                            </div>
                            <a v-else :href="data.evidence_url" target="_blank">{{ data.evidence }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <b :class="{ 'text-muted': data.status == 'submitted', 'text-danger': data.status == 'rejected', 'text-success': data.status == 'approved' }" class="text-capitalize">{{ data.status }}</b>
                        </td>
                    </tr>
                    <tr v-if="data.status == 'rejected'">
                        <th>Alasan Reject</th>
                        <td>{{ data.reject_description }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir diupdate oleh</th>
                        <td>{{ data.user_name }} ({{ data.user_name }})</td>
                    </tr>
                    <tr>
                        <th>Terakhir diupdate pada</th>
                        <td>{{ data.updated_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="showCloseButton" class="d-flex justify-content-end mt-5">
            <button type="button" @click="$emit('close')" class="btn btn-secondary">Tutup</button>
        </div>
    </section>
</template>
<style scoped>

.evidence-img {
    @apply tw-absolute tw-inset-0 tw-opacity-0;
}

.evidence-img :deep(img) {
    @apply tw-w-full tw-h-full;
}

</style>