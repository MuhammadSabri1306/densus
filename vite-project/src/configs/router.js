import { createRouter, createWebHistory } from "vue-router";

import Landing from "@views/Landing.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";

const useBuildPath = true;

const routes = [
    { path: "/", component: Landing },
    // { path: "/monitoring/detail/:rtuCode", component: MonitoringDetail }
    { path: "/monitoring/detail/:rtuCode", component: MonitoringDetail, params: { rtuCode: "RTU_BALA" } }
];

export default createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});