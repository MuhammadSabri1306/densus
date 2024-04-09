import { defineStore } from "pinia";
import { http, createHttpInstance } from "@helpers/http-common/http";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { createUrlParams } from "@/helpers/url";

export const useMonitoringRtuStore = defineStore("monitoring-rtu", {
    state: () => ({

        rtuKey: null,
        rtu: null,
        kwhCurrent: null,
        kwhSummary: null,
        kwhSaving: null,
        kwhDailyUsages: [],
        plnCostMonthly: [],
        bbmCostMonthly: [],
        costEstimation: null,
        bbmPrice: null,

    }),
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            
            return { divre, witel };
        },

    },
    actions: {

        setRtuKey(rtuCode) {
            this.rtuKey = rtuCode;
        },

        async fetchRtu(callback = null) {
            try {
                const response = await http.get("/monitoring-rtu/rtu/" + this.rtuKey, this.fetchHeader);
                if(response.data.rtu) {
                    this.rtu = response.data.rtu;
                    callback && callback();
                    return;
                }
                console.warn(response.data);
                this.rtu = null;
                callback && callback();
            } catch(err) {
                handlingFetchErr(err);
                callback && callback();
            }
        },

        async fetchEnergyCost(callback = null) {
            try {
                const response = await http.get("/monitoring/energy-cost/" + this.rtuKey, this.fetchHeader);
                if(!response.data.success)
                    console.warn(response.data);

                this.kwhSummary = response.data.kwh_summary || null;
                this.kwhSaving = response.data.kwh_saving || null;
                this.kwhDailyUsages = response.data.kwh_daily_usages || [];
                this.plnCostMonthly = response.data.pln_cost_monthly || [];
                this.bbmCostMonthly = response.data.bbm_cost_monthly || [];
                this.costEstimation = response.data.cost_estimation || null;
                if(response.data.bbm_price !== this.bbmPrice)
                    this.bbmPrice = response.data.bbm_price || null;

                callback && callback();
            } catch(err) {
                handlingFetchErr(err);
                callback && callback();
            }
        },

        async fetchRealtimeKwh(portNo, callback = null) {
            const rtuSname = this.rtuKey;
            const params = {
                searchRtuSname: rtuSname,
                searchNoPort: portNo
            };
            try {
                const http = createHttpInstance("https://newosase.telkom.co.id/api/v1", {
                    headers: { "Accept": "application/json" }
                });

                const response = await http.get("/dashboard-service/dashboard/rtu/port-sensors", { params });
                if(response.data.result.payload?.length > 0) {
                    this.kwhCurrent = response.data.result.payload[0]?.value || null;
                    callback && callback();
                    return;
                }
                this.kwhCurrent = null;
                console.warn(response.data);
                callback && callback();
            } catch(err) {
                handlingFetchErr(err);
                callback && callback();
            }
        },

    }
});