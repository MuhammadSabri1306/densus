<script setup>
import { ref, onMounted, onUnmounted } from "vue";

defineEmits(["detail", "edit", "delete"])

const showActions = ref(false);
const hideActions = () => showActions.value = false;
onMounted(() => document.body.addEventListener("click", hideActions));
onUnmounted(() => document.body.addEventListener("click", hideActions));
</script>
<template>
    <div class="d-flex">
        <button type="button" class="btn-circle btn-dt-action" @click.stop="showActions = !showActions">
            <VueFeather type="chevron-left" size="1rem" class="me-1" />
        </button>
        <div :class="{ 'show': showActions }" class="dt-dropdown">
            <button type="button" @click.stop="$emit('detail')" class="btn-circle btn btn-primary shadow p-0">
                <VueFeather type="eye" />
            </button>
            <button type="button" @click.stop="$emit('edit')" class="btn-circle btn btn-secondary shadow p-0">
                <VueFeather type="edit-2" />
            </button>
            <button type="button" @click.stop="$emit('delete')" class="btn-circle btn btn-danger shadow p-0">
                <VueFeather type="trash" />
            </button>
        </div>
    </div>
</template>
<style scoped>

.btn-dt-action {
    @apply tw-bg-transparent hover:tw-bg-gray-200 focus:tw-ring-primary/60;
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

.dt-dropdown.show > button:first-child {
    right: calc(100% + 9rem);
}

.dt-dropdown.show > button:nth-child(2) {
    right: calc(100% + 6rem);
}

.dt-dropdown.show > button:nth-child(3) {
    right: calc(100% + 3rem);
}

</style>