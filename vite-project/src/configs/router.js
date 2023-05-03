import { createRouter, createWebHistory } from "vue-router";

// import Landing from "@views/Landing.vue";
import Dashboard from "@views/Dashboard.vue";
import User from "@views/User.vue";
import Login from "@views/Login.vue";
import ErrorNotFound from "@views/ErrorNotFound.vue";

import Monitoring from "@views/Monitoring.vue";
import MonitoringDetail from "@views/MonitoringDetail.vue";

import Monitoring2 from "@views/Monitoring2.vue";
import MonitoringPlnParams from "@views/MonitoringPlnParams.vue";
// import MonitoringPlnParamsDetail from "@views/MonitoringPlnParamsDetail.vue";
import MonitoringPlnBilling from "@views/MonitoringPlnBilling.vue";
import MonitoringPlnBillingDetail from "@views/MonitoringPlnBillingDetail.vue";
import MonitoringFuelParams from "@views/MonitoringFuelParams.vue";
import MonitoringFuelParamsDetail from "@views/MonitoringFuelParamsDetail.vue";
import MonitoringFuelInvoice from "@views/MonitoringFuelInvoice.vue";
import MonitoringFuelInvoiceDetail from "@views/MonitoringFuelInvoiceDetail.vue";

import PueMonitoringRtu from "@views/PueMonitoringRtu.vue";
import PueMonitoringDivre from "@views/PueMonitoringDivre.vue";
import PueMonitoringWitel from "@views/PueMonitoringWitel.vue";
import PueMonitoring from "@views/PueMonitoring.vue";
import PueOffline from "@views/PueOffline.vue";
import PueOfflineDetail from "@views/PueOfflineDetail.vue";

import Rtu from "@views/Rtu.vue";
import RtuAdd from "@views/RtuAdd.vue";
import RtuEdit from "@views/RtuEdit.vue";

import ActivityDashboard from "@views/ActivityDashboard.vue";
import ActivitySchedule from "@views/ActivitySchedule.vue";
import ActivityExecution from "@views/ActivityExecution.vue";

import GepeeEvidence from "@views/GepeeEvidence.vue";
import GepeeEvidenceDivre from "@views/GepeeEvidenceDivre.vue";
import GepeeEvidenceWitel from "@views/GepeeEvidenceWitel.vue";

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
        path: "/monitoring-v2", component: Monitoring2,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-pln/billing", component: MonitoringPlnBilling,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-pln/billing/:locationId", component: MonitoringPlnBillingDetail,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-pln/params", component: MonitoringPlnParams,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-fuel/params", component: MonitoringFuelParams,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-fuel/params/:locationId", component: MonitoringFuelParamsDetail,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-fuel/invoice", component: MonitoringFuelInvoice,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/monitoring-fuel/invoice/:locationId", component: MonitoringFuelInvoiceDetail,
        meta: { menuKey: ["energy"], requiresAuth: true }
    },
    {
        path: "/pue/online", component: PueMonitoring,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/online/divre/:divreCode", component: PueMonitoringDivre,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/online/witel/:witelCode", component: PueMonitoringWitel,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/online/rtu/:rtuCode", component: PueMonitoringRtu,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/offline", component: PueOffline,
        meta: { menuKey: ["pue"], requiresAuth: true }
    },
    {
        path: "/pue/offline/:locationId", component: PueOfflineDetail,
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
        path: "/gepee-evidence", component: GepeeEvidence,
        meta: { menuKey: ["gepee_evidence"], requiresAuth: true }
    },
    {
        path: "/gepee-evidence/divre/:divreCode", component: GepeeEvidenceDivre,
        meta: { menuKey: ["gepee_evidence"], requiresAuth: true }
    },
    {
        path: "/gepee-evidence/witel/:witelCode", component: GepeeEvidenceWitel,
        meta: { menuKey: ["gepee_evidence"], requiresAuth: true }
    },
    {
        path: "/gepee-evidence/witel/:witelCode/:idCategory", component: GepeeEvidenceWitel,
        meta: { menuKey: ["gepee_evidence"], requiresAuth: true }
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