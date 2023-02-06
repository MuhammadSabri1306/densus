import { createRouter, createWebHistory } from "vue-router";
import Landing from "@views/Landing.vue";

const useBuildPath = true;

const routes = [
    { path: "/", component: Landing }
];

export default createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});