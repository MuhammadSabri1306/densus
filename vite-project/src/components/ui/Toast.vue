<script setup>
import { ref, computed, watch } from "vue";
import { useViewStore } from "@/stores/view";

const viewStore = useViewStore();
const toastData = computed(() => viewStore.toast);
const openToast = ref(false);
const showToast = ref(false);

const duration = {
	show: 100,
	hide: 6000
};

watch(() => viewStore.toast, () => {
	openToast.value = true;
	setTimeout(() => showToast.value = true, duration.show);
	setTimeout(() => {
		
		if(showToast.value)
			showToast.value = false;

	}, duration.show + duration.hide);
});
</script>
<template>
	<div v-if="openToast" class="tw-toast">
		<Transition name="fade" @after-leave="openToast = false">
			<div v-show="showToast" :class="{ 'toast-success': toastData.success, 'toast-error': !toastData.success }" class="toast-body">
				<span class="toast-icon">
					<slot name="icon"></slot>
                    <svg v-if="!$slots.icon && toastData.success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg v-if="!$slots.icon && !toastData.success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
				</span>
				<p class="toast-content">
					<span class="toast-title">{{ toastData.title }}</span>
					<span class="toast-message">{{ toastData.message }}</span>
				</p>
				<button type="button" @click="showToast = false" class="toast-close">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
				</button>
			</div>
		</Transition>
	</div>
</template>
<style scoped>
	
.tw-toast {
	@apply tw-max-w-[100vw] tw-max-h-screen tw-w-[468px] tw-p-4 md:tw-p-8 tw-fixed tw-z-[8888] tw-bottom-0 tw-right-0;
}

.toast-body {
	@apply tw-backdrop-blur-sm tw-shadow-lg tw-rounded tw-px-4 tw-py-6 tw-border tw-flex tw-gap-4 tw-items-start;
}

.toast-success {
	@apply tw-bg-success/80 tw-border-success;
}

.toast-error {
	@apply tw-bg-danger/80 tw-border-danger;
}

.toast-icon svg {
	@apply tw-w-10 tw-h-10;
}

.toast-icon,
.toast-content,
.toast-close {
	@apply tw-text-white;
	text-shadow: 0 0 2px rgba(0,0,0,0.6);
}

.toast-icon {
	@apply tw-opacity-60;
}

.toast-content {
	@apply tw-grow tw-flex tw-flex-col tw-gap-2;
}

.toast-title {
	@apply tw-font-bold;
}

.toast-message {
	@apply tw-text-sm;
}

.toast-close {
	@apply tw-p-2 tw-transition-opacity tw-opacity-70 hover:tw-opacity-100;
}

.toast-close svg {
	@apply tw-w-6 tw-h-6;
}

.fade-enter-active,
.fade-leave-active {
	transition: margin-top 0.3s, margin-bottom 0.3s, opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
	@apply tw-mt-4 -tw-mb-4 tw-opacity-0;
}

</style>