<script setup>
import { ref } from "vue";
import Loader from "@components/Loader.vue";
import LayoutDashboard from "@/layouts/Dashboard.vue";

const isLoading = ref(true);
const hideLoader = () => {
	isLoading.value = false;
};
</script>
<template>
	<Suspense @fallback="hideLoader">
		<LayoutDashboard>
			<template #main>
				<RouterView/>
			</template>
		</LayoutDashboard>
	</Suspense>
	<Loader v-if="isLoading" />
</template>