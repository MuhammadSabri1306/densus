import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { backendUrl } from "@/configs/base";

import { allowSampleData } from "@/configs/base";
import pueOfflineByLocation from "@helpers/sample-data/pue/offline_by_location";

export const usePueV2Store = defineStore("pueV2", {
    state: () => {

        const currDate = new Date();
        return {
            currDivre: null,
            currWitel: null,
            currRtu: null,
            idLocation: null
        };

    },
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        zoneUrlParams: state => {
            if(state.currRtu)
                return "/?rtu=" + state.currRtu;
            if(state.currWitel)
                return "/?witel=" + state.currWitel;
            if(state.currDivre)
                return "/?divre=" + state.currDivre;
            return "";
        },

        filters: state => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            const idLocation = state.idLocation;
            
            return { divre, witel, idLocation, year, month };
        },
        
        offlineUrlParams() {
            const filters = this.filters;

            const params = [];
            if(filters.divre)
                params.push("divre=" + filters.divre);
            if(filters.witel)
                params.push("witel=" + filters.witel);
            if(filters.month)
                params.push("month=" + filters.month);
            if(filters.year)
                params.push("year=" + filters.year);
            if(filters.idLocation)
                params.push("idLocation=" + filters.idLocation);

            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        excelExportUrl() {
            const urlParams = this.zoneUrlParams;
            console.log(urlParams)
            return backendUrl + "/export/excel/pue" + urlParams;
        },

        monthList: () => {
            const viewStore = useViewStore();
            return viewStore.monthList;
        }

    },
    actions: {

        setCurrentZone(zone) {
            this.currRtu = null;
            this.currWitel = null;
            this.currDivre = null;
            this.idLocation = null;

            if(zone.rtu) this.currRtu = zone.rtu;
            else if(zone.witel) this.currWitel = zone.witel;
            else if(zone.divre) this.currDivre = zone.divre;

            if(zone.idLocation) this.idLocation = zone.idLocation;
        },

        async getChartData(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/chart_data" + zoneUrlParams, this.fetchHeader);
                if(!response.data.chart) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async getLatestPue(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/latest_value" + zoneUrlParams, this.fetchHeader);
                if(!response.data.latestValue) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async getOfflineLocationData(callback) {
            const urlParams = this.offlineUrlParams;
            try {
                const response = await http.get("/pue/offline/location" + urlParams, this.fetchHeader);
                if(!response.data.location_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async createOfflineLocation(body, callback = null) {
            try {
                const response = await http.post("/pue/offline/location", body, this.fetchHeader);
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

        async updateOfflineLocation(idLocation, body, callback = null) {
            try {
                const response = await http.put("/pue/offline/location" + idLocation, body, this.fetchHeader);
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

        async deleteOfflineLocation(idLocation, callback = null) {
            try {
                const response = await http.delete("/pue/offline/location/" + idLocation, this.fetchHeader);
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

        async getOfflinePue(callback) {
            try {
                const response = await http.get("/pue/offline/" + this.idLocation, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: pueOfflineByLocation });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async createOffline(body, callback = null) {
            try {
                const response = await http.post("/pue/offline", body, this.fetchHeader);
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

        async updateOffline(id, body, callback = null) {
            try {
                const response = await http.put("/pue/offline/" + id, body, this.fetchHeader);
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

        async deleteOffline(id, callback = null) {
            try {
                const response = await http.delete("/pue/offline/" + id, this.fetchHeader);
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