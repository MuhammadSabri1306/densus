<script setup>
import { computed, onBeforeMount } from "vue";
import { useRoute } from "vue-router";
import LayoutDashboard from "@layouts/Dashboard.vue";
import LayoutSingle from "@layouts/SinglePage.vue";

const route = useRoute();
const layout = computed(() => {
	const routeName = route.name;
	return ["login", "e404"].indexOf(routeName) < 0 ? LayoutDashboard : LayoutSingle;
});

onBeforeMount(() => {
    const loaderElm = document.getElementById("loader");
	if(!loaderElm)
	    return;
	
	loaderElm.classList.add("hide");
	setTimeout(function(){
	    loaderElm.remove();
    }, 700);
});
</script>
<template>
    <component :is="layout"/>
</template>