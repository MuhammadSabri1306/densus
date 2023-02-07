<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import Loader from "@components/Loader.vue";
import LayoutDashboard from "@/layouts/Dashboard.vue";
import Toast from "@components/ui/Toast.vue";

const isLoading = ref(true);
const hideLoader = () => {
	isLoading.value = false;
};

const viewStore = useViewStore();
const router = useRouter();
router.beforeEach((to, from) => {
	if(to.meta.menuKey !== from.meta.menuKey)
		viewStore.setActiveMenu(to.meta.menuKey);
});
</script>
<template>
	<Suspense @fallback="hideLoader">
		<LayoutDashboard>
			<template #main>
				<RouterView/>
			</template>
		</LayoutDashboard>
	</Suspense>
	<Toast />
	<Loader v-if="isLoading" />
</template>