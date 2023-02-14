<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import Loader from "@components/Loader.vue";
import LayoutDashboard from "@/layouts/Dashboard.vue";
import LayoutSingle from "@/layouts/SinglePage.vue";
import Toast from "@components/ui/Toast.vue";

const isLoading = ref(true);
const hideLoader = () => {
	isLoading.value = false;
};

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

const route = useRoute();
const layout = computed(() => {
	const routeName = route.name;
	return ["login", "e404"].indexOf(routeName) < 0 ? LayoutDashboard : LayoutSingle;
});

// LOCAL TESTING
window.setAuthManually = token => {
	userStore.sync({
		id: 5,
		name: "Developer",
		role: "dev123",
		level: "nasional",
		location: null,
		token
	});
};
</script>
<template>
	<Suspense @fallback="hideLoader">
		<component :is="layout">
			<template #main>
				<RouterView/>
			</template>
		</component>
	</Suspense>
	<Toast />
	<Loader v-if="isLoading" />
</template>