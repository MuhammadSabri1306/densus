import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

import { allowSampleData } from "@/configs/base";
import samplePueTargetReport from "@helpers/sample-data/pue-target/report";

export const usePueTargetStore = defineStore("pue-target", {
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: state => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const quarter = viewStore.filters.quarter;
            
            return { divre, witel, year, quarter };
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
            if(exclude.indexOf("quarter") < 0 && filters.quarter)
                params.push("quarter=" + filters.quarter);

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

        async getTarget(callback) {
            const urlParams = this.getUrlParams(["quarter"]);
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
        }

    }
});