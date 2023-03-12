import { createRouter, createWebHistory } from "vue-router";

// import Landing from "@views/Landing.vue";
import Dashboard from "@views/Dashboard.vue";
import Monitoring from "@views/Monitoring.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";
import MonitoringPue from "@views/MonitoringPue.vue";
import MonitoringPueDetail from "@views/MonitoringPueDetail.vue";
import Rtu from "@views/Rtu.vue";
import RtuAdd from "@views/RtuAdd.vue";
import RtuEdit from "@views/RtuEdit.vue";
import ActivityDashboard from "@views/ActivityDashboard.vue";
import ActivitySchedule from "@views/ActivitySchedule.vue";
import ActivityExecution from "@views/ActivityExecution.vue";
import User from "@views/User.vue";

import Login from "@views/Login.vue";
import ErrorNotFound from "@views/ErrorNotFound.vue";

const routes = [
    {
        path: "/", component: Dashboard,
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
        path: "/pue", component: MonitoringPue,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/:rtuCode", component: MonitoringPueDetail,
        meta: { menuKey: ["pue"], requiresAuth: true }
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
        path: "/gepee", component: ActivityDashboard,
        meta: { menuKey: ["gepee", "dashboard"], requiresAuth: true }
    },
    {
        path: "/gepee/:scheduleId(\\d+)", component: ActivityDashboard,
        meta: { menuKey: ["gepee", "dashboard"], requiresAuth: true }
    },
    {
        path: "/gepee/schedule", component: ActivitySchedule,
        meta: { menuKey: ["gepee", "schedule"], requiresAuth: true }
    },
    {
        path: "/gepee/exec", component: ActivityExecution, name: "gepeeExecTable",
        meta: { menuKey: ["gepee", "exec"], requiresAuth: true }
    },
    {
        path: "/gepee/exec/:scheduleId", component: ActivityExecution, name: "gepeeExecList",
        meta: { menuKey: ["gepee", "exec"], requiresAuth: true }
    },
    {
        path: "/gepee/exec/:scheduleId/add", component: ActivityExecution, name: "gepeeExecAdd",
        meta: { menuKey: ["gepee", "exec"], requiresAuth: true }
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