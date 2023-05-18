<script setup>
import { ref, onMounted, onUnmounted } from "vue";

defineEmits(["detail", "edit", "delete"]);
const props = defineProps({
    disable: { type: Boolean, default: false },
    useBtnDetail: { type: Boolean, default: true },
    disableBtnDetail: { type: Boolean, default: false },
    useBtnEdit: { type: Boolean, default: true },
    disableBtnEdit: { type: Boolean, default: false },
    useBtnDelete: { type: Boolean, default: true },
    disableBtnDelete: { type: Boolean, default: false }
});

const showActions = ref(false);
const hideActions = () => showActions.value = false;
onMounted(() => document.body.addEventListener("click", hideActions));
onUnmounted(() => document.body.addEventListener("click", hideActions));
</script>
<template>
    <div class="d-flex">
        <button type="button" class="btn-circle btn-dt-action" @click.stop="showActions = !showActions" :disabled="disable">
            <VueFeather type="chevron-left" size="1rem" class="me-1" />
        </button>
        <div :class="{ 'show': showActions }" class="dt-dropdown">
            <button v-if="useBtnDetail" type="button" @click.stop="$emit('detail')" :disabled="disableBtnDetail" class="btn-circle btn btn-primary shadow p-0">
                <VueFeather type="eye" />
            </button>
            <button v-if="useBtnEdit" type="button" @click.stop="$emit('edit')" :disabled="disableBtnEdit" class="btn-circle btn btn-secondary shadow p-0">
                <VueFeather type="edit-2" />
            </button>
            <button v-if="useBtnDelete" type="button" @click.stop="$emit('delete')" :disabled="disableBtnDelete" class="btn-circle btn btn-danger shadow p-0">
                <VueFeather type="trash" />
            </button>
        </div>
    </div>
</template>
<style scoped>

.btn-dt-action {
    @apply tw-bg-transparent disabled:tw-opacity-50 enabled:hover:tw-bg-gray-200 focus:tw-ring-primary/60;
}

.dt-dropdown {
    @apply tw-relative;
}

.dt-dropdown > button {
    @apply tw-absolute tw-top-0 tw-right-[-100%] tw-opacity-0 tw-w-0 tw-z-[22];
    transition: right 0.3s, opacity 0.3s, width 0.3s;
}

.dt-dropdown.show > button {
    @apply tw-opacity-100 tw-w-10;
}

.dt-dropdown > button[disabled] > :deep(i) {
    @apply tw-opacity-60;
}

.dt-dropdown.show > button:nth-last-child(3) {
    right: calc(100% + 9rem);
}

.dt-dropdown.show > button:nth-last-child(2) {
    right: calc(100% + 6rem);
}

.dt-dropdown.show > button:last-child {
    right: calc(100% + 3rem);
}

</style>