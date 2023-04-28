import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { backendUrl } from "@/configs/base";

import { allowSampleData } from "@/configs/base";
import samplePueDivre from "@helpers/sample-data/pue/divre";
import samplePueWitel from "@helpers/sample-data/pue/witel";
import samplePueRtu from "@helpers/sample-data/pue/rtu";

export const usePueStore = defineStore("pue", {
    state: () => ({

        pue: null,
        lastFetch: null,
        currDivre: null,
        currWitel: null,
        currRtu: null

    }),
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

        excelExportUrl() {
            const urlParams = this.zoneUrlParams;
            console.log(urlParams)
            return backendUrl + "/export/excel/pue" + urlParams;
        }

    },
    actions: {

        async fetchOnNasional(force = false, callback = null) {
            if(this.pue && this.lastFetch == "nasional" && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            this.pue = null;
            try {
                const response = await http.get("/monitoring/pue_v2", this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.pue = response.data.pue;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.pue = samplePueDivre.pue;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },
        
        async fetchOnDivre(divreCode, force = false, callback = null) {
            if(this.pue && this.lastFetch == divreCode && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            this.pue = null;
            try {
                const response = await http.get("/monitoring/pue_v2/?divre=" + divreCode, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.pue = response.data.pue;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.pue = samplePueDivre.pue;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async fetchOnWitel(witelCode, force = false, callback = null) {
            if(this.pue && this.lastFetch == witelCode && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            this.pue = null;
            try {
                const response = await http.get("/monitoring/pue_v2/?witel=" + witelCode, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.pue = response.data.pue;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.pue = samplePueDivre.pue;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async fetchOnRtu(rtuCode, force = false, callback = null) {
            if(this.pue && this.lastFetch == rtuCode && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            this.pue = null;
            try {
                const response = await http.get("/monitoring/pue_v2/?rtu=" + rtuCode, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.pue = response.data.pue;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.pue = samplePueDivre.pue;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        setCurrentZone(zone) {
            this.currRtu = null;
            this.currWitel = null;
            this.currDivre = null;

            if(zone.rtu) this.currRtu = zone.rtu;
            else if(zone.witel) this.currWitel = zone.witel;
            else if(zone.divre) this.currDivre = zone.divre;
        },

        async agetValueOnYear(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/value_on_year" + zoneUrlParams, this.fetchHeader);
                if(!response.data.valueOnYear) {
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
        }

    }
});