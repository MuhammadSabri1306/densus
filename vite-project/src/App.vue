<script setup>
import { computed, defineAsyncComponent } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import { useUserStore } from "@stores/user";
import Toast from "@components/ui/Toast.vue";

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
</script>
<template>
	<Layout />
	<Toast />
</template>