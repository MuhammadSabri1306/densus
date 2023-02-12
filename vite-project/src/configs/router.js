import { createRouter, createWebHistory } from "vue-router";

import Landing from "@views/Landing.vue";
import Monitoring from "@views/Monitoring.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";
import Rtu from "@views/Rtu.vue";
import RtuAdd from "@views/RtuAdd.vue";
import RtuEdit from "@views/RtuEdit.vue";
import User from "@views/User.vue";

import Login from "@views/Login.vue";
import ErrorNotFound from "@views/ErrorNotFound.vue";

const routes = [
    {
        path: "/", component: Landing,
        meta: { menuKey: ["landing"], requiresAuth: true }
    },
    {
        path: "/monitoring", component: Monitoring,
        meta: { menuKey: ["monitoring"], requiresAuth: true }
    },
    {
        path: "/monitoring/:rtuCode", component: MonitoringDetail,
        meta: { menuKey: ["monitoring"], requiresAuth: true }
    },
    {
        path: "/rtu", component: Rtu,
        meta: { menuKey: ["rtu", "list"], requiresAuth: true }
    },
    {
        path: "/rtu/detail/:rtuId", component: RtuEdit,
        meta: { menuKey: ["rtu", "list"], requiresAuth: true }
    },
    {
        path: "/rtu/add", component: RtuAdd,
        meta: { menuKey: ["rtu", "add"], requiresAuth: true }
    },
    {
        path: "/user", component: User,
        meta: { menuKey: ["user"], requiresAuth: true }
    },
    {
        path: "/login", name: "login", component: Login,
        meta: { requiresAuth: false }
    },
    {
        path: "/:pathMatch(.*)*", name: "e404", component: ErrorNotFound,
        meta: { requiresAuth: false }
    }
];

export default createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});