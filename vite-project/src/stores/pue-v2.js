import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { backendUrl } from "@/configs/base";

import { allowSampleData } from "@/configs/base";

export const usePueV2Store = defineStore("pueV2", {
    state: () => {

        const currDate = new Date();
        return {
            currDivre: null,
            currWitel: null,
            currRtu: null,
            filters: {
                divre: null,
                witel: null,
                idLocation: null,
                year: currDate.getFullYear(),
                month: currDate.getMonth() + 1
            }
        };

    },
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            return viewStore.filters;
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

        offlineUrlParams: state => {
            const divre = state.filters.divre;
            const witel = state.filters.witel;
            const idLocation = state.filters.idLocation;
            const year = state.filters.year;
            const month = state.filters.month;

            if(state.filters.divre)
                params.push("divre=" + divre);
            if(state.filters.witel)
                params.push("witel=" + witel);
            if(state.filters.idLocation)
                params.push("idLocation=" + idLocation);
            if(state.filters.month)
                params.push("month=" + month);
            if(state.filters.year)
                params.push("year=" + year);

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

            if(zone.rtu) this.currRtu = zone.rtu;
            else if(zone.witel) this.currWitel = zone.witel;
            else if(zone.divre) this.currDivre = zone.divre;
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
        }

    }
});