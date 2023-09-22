<script setup>
import { computed, defineAsyncComponent } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import Toast from "@components/ui/Toast.vue";
import LoadingWaveBar from "@/components/ui/LoadingWaveBar.vue";

const userStore = useUserStore();
userStore.readUserCookie();
const hasToken = computed(() => userStore.hasToken);

const viewStore = useViewStore();
const router = useRouter();

router.beforeEach((to, from) => {
	if(to.meta.requiresAuth && !hasToken.value)
        router.push({ path: "/login", query: { redirect: to.fullPath } });

	if(to.meta.menuKey !== from.meta.menuKey)
		viewStore.setActiveMenu(to.meta.menuKey);
});

const Layout = defineAsyncComponent(() => import("@layouts/index.vue"));
const showLoadingLayer = computed(() => viewStore.showLoading);
</script>
<template>
	<Layout />
	<Toast />
	<Teleport to="body">
		<Transition name="loading">
			<div v-if="showLoadingLayer" class="loading-layer">
				<LoadingWaveBar class="tw-w-72 lg:tw-w-96 tw-h-24 lg:tw-h-36" barClass="bg-primary" />
			</div>
		</Transition>
		<Toast />
	</Teleport>
</template>
<style scoped>

.loading-layer {
	@apply tw-fixed tw-inset-0 tw-z-[9998] tw-flex tw-justify-center tw-items-center tw-bg-white/10 tw-backdrop-blur;
}

.loading-enter-active, .loading-leave-active {
	@apply tw-transition-opacity tw-duration-500;
}

.loading-enter-from, .loading-leave-to {
	@apply tw-opacity-0;
}

</style>