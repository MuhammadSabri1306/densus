import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

import { allowSampleData } from "@/configs/base";
import samplePueTargetReport from "@helpers/sample-data/pue-target/report";
import samplePueLocationStatus from "@helpers/sample-data/pue-target/location-status";

export const usePueTargetStore = defineStore("pue-target", {
    state: () => ({

        isLoading: {
            report: false
        },
        target: null,
        category: null

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
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            
            return { divre, witel, year, month };
        },

    },
    actions: {

        getUrlParams(...exclude) {
            const filters = this.filters;

            const params = [];
            if(exclude.indexOf("divre") < 0 && filters.divre)
                params.push("divre=" + filters.divre);
            if(exclude.indexOf("witel") < 0 && filters.witel)
                params.push("witel=" + filters.witel);
            if(exclude.indexOf("year") < 0 && filters.year)
                params.push("year=" + filters.year);
            if(exclude.indexOf("month") < 0 && filters.month)
                params.push("month=" + filters.month);

            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        async getReport(callback) {
            const urlParams = this.getUrlParams();
            const url = "/pue-target/report" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pue_target) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: samplePueTargetReport });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async fetchReport(force = false, callback = null) {
            this.isLoading.report = true;
            if(!force && this.report) {
                this.isLoading.report = false;
                callback && callback(true);
                return;
            }

            const urlParams = this.getUrlParams();
            const url = "/pue-target/report" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pue_target) {
                    console.warn(response.data);
                    callback && callback(false);
                    return;
                }

                this.target = response.data.pue_target;
                this.category = response.data.pue_category;
                this.isLoading.report = false;
                
                callback && callback(true);

            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData) {
                    this.target = samplePueTargetReport.pue_target;
                    this.category = samplePueTargetReport.pue_category;
                    this.isLoading.report = false;
                    callback && callback(true);
                    return;
                }

                this.isLoading.report = false;
                callback && callback(false);
            }
        },

        async getTarget(callback) {
            const urlParams = this.getUrlParams(["month"]);
            const url = "/pue-target" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pue_target) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: samplePueTargetReport });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async create(body, callback = null) {
            try {
                const response = await http.post("/pue-target", body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async update(id, body, callback = null) {
            try {
                const response = await http.put("/pue-target/" + id, body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async delete(id, callback = null) {
            try {
                const response = await http.delete("/pue-target/" + id, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async getLocationStatus(callback) {
            const urlParams = this.getUrlParams("year", "quarter");
            const url = "/pue-target/report/location-status" + urlParams;
            const result = { success: true, status: false, data: {} };
            try {
    
                const response = await http.get(url, this.fetchHeader);
                result.status = response.status;
                if(!response.data.pue_status) {
                    result.success = false;
                    console.warn(response.data);
                }
                result.data = response.data;
    
            } catch(err) {
                
                handlingFetchErr(err);
                if(allowSampleData)
                    result.data = samplePueLocationStatus;
                result.status = err.response?.status;
                result.success = false;
    
            } finally {
                callback(result);
            }
        }

    }
});