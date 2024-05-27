import { createRouter, createWebHistory } from "vue-router";

import Dashboard from "@/views/Dashboard.vue";
import User from "@/views/User.vue";
import Login from "@/views/Login.vue";
import ErrorNotFound from "@/views/ErrorNotFound.vue";
import ErrorMaintenance from "@/views/ErrorMaintenance.vue";

import MonitoringRtu from "@/views/MonitoringRtu.vue";
import MonitoringRtuDetail from "@/views/MonitoringRtuDetail.vue";

import Monitoring2 from "@/views/Monitoring2.vue";
import MonitoringPlnParams from "@/views/MonitoringPlnParams.vue";
import MonitoringPlnBilling from "@/views/MonitoringPlnBilling.vue";
import MonitoringPlnBillingDetail from "@/views/MonitoringPlnBillingDetail.vue";
import MonitoringFuelParams from "@/views/MonitoringFuelParams.vue";
import MonitoringFuelParamsDetail from "@/views/MonitoringFuelParamsDetail.vue";
import MonitoringFuelInvoice from "@/views/MonitoringFuelInvoice.vue";
import MonitoringFuelInvoiceDetail from "@/views/MonitoringFuelInvoiceDetail.vue";

import PueOnlineV2 from "@/views/PueOnlineV2.vue";
import PueOffline from "@/views/PueOffline.vue";
import PueOfflineDetail from "@/views/PueOfflineDetail.vue";
import Ike from "@/views/Ike.vue";
import IkeDetail from "@/views/IkeDetail.vue";

import MonitoringKwHVue from "@/views/MonitoringKwH.vue";

import Rtu from "@/views/Rtu.vue";
import RtuAdd from "@/views/RtuAdd.vue";
import RtuEdit from "@/views/RtuEdit.vue";

import ActivityDashboard from "@/views/ActivityDashboard.vue";
import ActivityScheduleV2 from "@/views/ActivityScheduleV2.vue";
import ActivityExecution from "@/views/ActivityExecution.vue";

import GepeeEvidence from "@/views/GepeeEvidence.vue";
import GepeeEvidenceDivre from "@/views/GepeeEvidenceDivre.vue";
import GepeeEvidenceWitel from "@/views/GepeeEvidenceWitel.vue";

import GepeeReport from "@/views/GepeeReport.vue";
import GepeeReportV2 from "@/views/GepeeReportV2.vue";
import PueTarget from "@/views/PueTarget.vue";
import PueTargetDetail from "@/views/PueTargetDetail.vue";
import GepeePiLatenV2 from "@/views/GepeePiLatenV2.vue";

import OxispActivity from "@/views/OxispActivity.vue";
import OxispChecklist from "@/views/OxispChecklist.vue";

import TestAsyncComponent from "@/views/TestAsyncComponent.vue";

const routes = [
    {
        path: "/", component: Dashboard,
        meta: { menuKey: ["landing"], requiresAuth: true }
    },
    {
        path: "/monitoring-rtu", component: MonitoringRtu,
        meta: { menuKey: ["monitoring_rtu"], requiresAuth: true }
    },
    {
        path: "/monitoring-rtu/:rtuCode", component: MonitoringRtuDetail,
        meta: { menuKey: ["monitoring_rtu"], requiresAuth: true }
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
        path: "/pue/online/", component: PueOnlineV2,
        meta: { menuKey: ["pue", "online"], requiresAuth: true }
    },
    {
        path: "/pue/offline", component: PueOffline,
        meta: { menuKey: ["pue", "offline"], requiresAuth: true }
    },
    {
        path: "/pue/offline/:locationId", component: PueOfflineDetail,
        meta: { menuKey: ["pue", "offline"], requiresAuth: true }
    },
    {
        path: "/ike", component: Ike,
        meta: { menuKey: ["pue", "ike"], requiresAuth: true }
    },
    {
        path: "/ike/:locationId", component: IkeDetail,
        meta: { menuKey: ["pue", "ike"], requiresAuth: true }
    },
    {
        path: "/monitoring-kwh", component: MonitoringKwHVue,
        meta: { menuKey: ["kwh"], requiresAuth: true }
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
        path: "/gepee/schedule", component: ActivityScheduleV2,
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
        name: "GepeeReportV1",
        path: "/gepee-performance/management-report", component: GepeeReport,
        meta: { menuKey: ["gepee_performance", "report"], requiresAuth: true }
    },
    {
        name: "GepeeReportV2",
        path: "/gepee-performance/management-reportv2", component: GepeeReportV2,
        meta: { menuKey: ["gepee_performance", "report"], requiresAuth: true }
    },
    {
        path: "/gepee-performance/pue-target", component: PueTarget,
        meta: { menuKey: ["gepee_performance", "pue_target"], requiresAuth: true }
    },
    {
        path: "/gepee-performance/pue-target/target", component: PueTargetDetail,
        meta: { menuKey: ["gepee_performance", "pue_target", "target"], requiresAuth: true }
    },
    {
        path: "/gepee-performance/pi-laten", component: GepeePiLatenV2,
        meta: { menuKey: ["gepee_performance", "pi_laten"], requiresAuth: true }
    },
    {
        path: "/oxisp/activity/:year(\\d+)/:month(\\d+)/:idLocation(\\d+)", component: OxispActivity,
        meta: { menuKey: ["oxisp", "activity"], requiresAuth: true }
    },
    {
        path: "/oxisp/activity/:idLocation(\\d+)", component: OxispActivity,
        meta: { menuKey: ["oxisp", "activity"], requiresAuth: true }
    },
    {
        path: "/oxisp/activity", component: OxispActivity,
        meta: { menuKey: ["oxisp", "activity"], requiresAuth: true }
    },
    {
        path: "/oxisp/checklist", component: OxispChecklist,
        meta: { menuKey: ["oxisp", "checklist"], requiresAuth: true }
    },
    {
        path: "/login", name: "login", component: Login,
        meta: { requiresAuth: false }
    },
    {
        path: "/test", name: "test", component: TestAsyncComponent,
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