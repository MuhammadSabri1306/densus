import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { createUrlParams } from "@/helpers/url";

import { allowSampleData } from "@/configs/base";
import sampleKwhDaily from "@helpers/sample-data/monitoring-kwh/daily";
import sampleKwhWeekly from "@helpers/sample-data/monitoring-kwh/weekly";
import sampleKwhMonthly from "@helpers/sample-data/monitoring-kwh/monthly";
import sampleKwhMoM from "@helpers/sample-data/monitoring-kwh/mom";

export const useKwhStore = defineStore("kwh", {
    state: () => {
        const viewStore = useViewStore();
        return {

            monthList: viewStore.monthList,
            periodicModes: [
                { value: "monthly", title: "Bulanan" },
                { value: "weekly", title: "Mingguan" },
                { value: "daily", title: "Harian" }
            ]

        };
    },
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            
            return { divre, witel, year, month };
        },

    },
    actions: {

        buildUrlParams(...keys) {
            const filters = this.filters;
            const params = {};

            keys.forEach(item => {

                if(typeof item == "string") {
                    const key = item;
                    if(filters[key] !== undefined && filters[key] !== null)
                        params[key] = filters[key];
                }

                if(typeof item == "object") {
                    for(let key in item) {
                        if(item[key] !== undefined && item[key] !== null)
                            params[key] = item[key];
                    }
                }

            });

            const urlParams = createUrlParams(params);
            return urlParams.length > 0 ? `?${ urlParams }` : "";
        },

        async getDaily(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "month", "year");
            try {

                const response = await http.get("/monitoring-kwh/daily" + urlParams, this.fetchHeader);
                if(!response.data.kwh_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleKwhDaily });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async getWeekly(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "month", "year");
            try {

                const response = await http.get("/monitoring-kwh/weekly" + urlParams, this.fetchHeader);
                if(!response.data.kwh_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleKwhWeekly });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async getMonthly(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "year");
            try {

                const response = await http.get("/monitoring-kwh/monthly" + urlParams, this.fetchHeader);
                if(!response.data.kwh_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleKwhMonthly });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async getMoM(callback) {
            const urlParams = this.buildUrlParams("divre", "witel");
            try {

                const response = await http.get("/monitoring-kwh/mom" + urlParams, this.fetchHeader);
                if(!response.data.mom) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleKwhMoM });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        }

    }
});