import { createRouter, createWebHistory } from "vue-router";

import Landing from "@views/Landing.vue";
import Monitoring from "@views/Monitoring.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";
import Rtu from "@views/Rtu.vue";
import RtuAdd from "@views/RtuAdd.vue";
import RtuEdit from "@views/RtuEdit.vue";
import User from "@views/User.vue";

const useBuildPath = true;

const routes = [
    {
        path: "/",
        component: Landing,
        meta: { menuKey: ["landing"] }
    },
    {
        path: "/monitoring",
        component: Monitoring,
        meta: { menuKey: ["monitoring"] }
    },
    {
        path: "/monitoring/:rtuCode",
        component: MonitoringDetail,
        meta: { menuKey: ["monitoring"] }
    },
    {
        path: "/rtu",
        component: Rtu,
        meta: { menuKey: ["rtu", "list"] }
    },
    {
        path: "/rtu/detail/:rtuId",
        component: RtuEdit,
        meta: { menuKey: ["rtu", "list"] }
    },
    {
        path: "/rtu/add",
        component: RtuAdd,
        meta: { menuKey: ["rtu", "add"] }
    },
    {
        path: "/user",
        component: User,
        meta: { menuKey: ["user", "list"] }
    }
];

export default createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});