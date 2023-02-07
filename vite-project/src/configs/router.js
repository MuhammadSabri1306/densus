import { createRouter, createWebHistory } from "vue-router";

import Landing from "@views/Landing.vue";
import Monitoring from "@views/Monitoring.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";
import Rtu from "@views/Rtu.vue";
import RtuAdd from "@views/RtuAdd.vue";
import RtuEdit from "@views/RtuEdit.vue";

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
    }
];

export default createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
});