import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { handlingFetchErr } from "@/helpers/error-handler";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";
import { backendUrl } from "@/configs/base";
import { createUrlParams } from "@/helpers/url";

import { allowSampleData } from "@/configs/base";
import getSampleData from "@/helpers/sample-data";
import samplePueOfflineByLocation from "@/helpers/sample-data/pue/offline_by_location";
import sampleLatestValue from "@/helpers/sample-data/pue/latest-value";
import sampleChartData from "@/helpers/sample-data/pue/chart-data";
import sampleMaxValue from "@/helpers/sample-data/pue/max-value";
import sampleAvg from "@/helpers/sample-data/pue/avg";
import samplePerformance from "@/helpers/sample-data/pue/performance";
import sampleStoValue from "@/helpers/sample-data/pue/sto-value";

export const usePueV2Store = defineStore("pueV2", {
    state: () => ({

        currDivre: null,
        currWitel: null,
        currRtu: null,

        requestLocation: null,
        chartData: null,

        isLoading: {
            chartData: false
        }
        
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

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            
            return { divre, witel, year, month };
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
            return backendUrl + "/export/excel/pue" + urlParams;
        },

        monthList: () => {
            const viewStore = useViewStore();
            return viewStore.monthList;
        },

        maxValue: state => {
            const chartData = JSON.parse(JSON.stringify(state.chartData));
            if(!chartData)
                return null;

            const descOrdered = chartData.sort((a, b) => b.pue_value - a.pue_value);
            return descOrdered[0];
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
            console.log(zone)
        },

        buildUrlParams(filters) {
            const params = [];
            for(let key in filters) {
                if(filters[key] !== null && filters[key] !== undefined)
                    params.push(`${ key }=${ filters[key] }`);
            }
            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        async fetchChartData(callback = null) {
            const url = "/pue/chart_data" + this.zoneUrlParams;
            this.isLoading.chartData = true;
            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.chart) {
                    console.warn(response.data);
                    callback && callback(false);
                    return;
                }
                
                this.chartData = response.data.chart;
                this.requestLocation = response.data.request_location;
                callback && callback(true);
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData) {
                    this.chartData = sampleChartData.chart;
                    this.requestLocation = sampleChartData.request_location;
                    console.log(this.requestLocation)
                    callback && callback(true);
                    return;
                }
                callback && callback(false);
            } finally {
                this.isLoading.chartData = false;
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
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleChartData });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async getLatestPue(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/latest_value" + zoneUrlParams, this.fetchHeader);
                if(response.data.latestValue === undefined) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleLatestValue });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async getMaxPue(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/max_value" + zoneUrlParams, this.fetchHeader);
                if(!response.data.maxValue) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleMaxValue });
                else
                    callback({ success: false, status: err.response?.status, data: {} });

            }
        },

        async getAvgPue(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            try {
                const response = await http.get("/pue/avg_value" + zoneUrlParams, this.fetchHeader);
                if(!response.data.averages) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleAvg });
                else
                    callback({ success: false, status: err.response?.status, data: {} });

            }
        },

        async getPerformance(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            let data = {};
            let status = null;
            try {
            
                const response = await http.get("/pue/performance" + zoneUrlParams, this.fetchHeader);
                status = response.status;
                if(!response.data.performances)
                    console.warn(response.data);
                else
                    data = response.data;
                    
            } catch(err) {
                handlingFetchErr(err);
                status = err.response?.status;
                if(allowSampleData)
                    data = samplePerformance;
            } finally {
                callback({ success: false, status, data });
            }
        },

        async getStoValue(callback) {
            const zoneUrlParams = this.zoneUrlParams;
            let data = {};
            let status = null;
            try {
            
                const response = await http.get("/pue/sto_value_on_year" + zoneUrlParams, this.fetchHeader);
                status = response.status;
                if(!response.data.stoValue)
                    console.warn(response.data);
                else
                    data = response.data;

            } catch(err) {
                handlingFetchErr(err);
                status = err.response?.status;
                if(allowSampleData)
                    data = sampleStoValue;
            } finally {
                callback({ success: false, status, data });
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

        async getOfflineLocationDataV2(callback) {
            const urlParams = this.offlineUrlParams;
            try {
                const response = await http.get("/pue/offline/v2" + urlParams, this.fetchHeader);
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
                const response = await http.put("/pue/offline/location/" + idLocation, body, this.fetchHeader);
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

        async getOfflinePue(idLocation, callback) {
            const filters = this.filters;
            const urlParams = this.buildUrlParams({ year: filters.year, month: filters.month });
            const url = "/pue/offline/" + idLocation + urlParams;

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: samplePueOfflineByLocation });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },
        
        async getOfflinePueDetail(idLocation, callback) {
            const filters = this.filters;
            const urlParams = this.buildUrlParams({ year: filters.year, month: filters.month });
            const url = "/pue/offline/v2/" + idLocation + urlParams;

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pue) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: samplePueOfflineByLocation });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async createOffline(body, callback = null) {
            const urlParams = this.buildUrlParams({ year: this.filters.year, month: this.filters.month });
            try {
                const response = await http.post("/pue/offline" + urlParams, body, this.fetchHeader);
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
        },

        async getPueOnlineMonitoringData(callback) {
            const params = {};
            if(this.filters.divre) params.divre = this.filters.divre;
            if(this.filters.witel) params.witel = this.filters.witel;
            const urlParams = createUrlParams(params);
            try {
                const response = await http.get("/pue/online?" + urlParams, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback(response.data);
                    return;
                }
                callback(response.data);
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData) {
                    const sampleData = await getSampleData("pue-online", {});
                    callback(sampleData);
                } else {
                    callback(null);
                }
            }
        },

        async getRtuPueOnline(rtuCode, callback) {
            try {
                const response = await http.get("/pue/online/" + rtuCode, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback(response.data);
                    return;
                }
                callback(response.data);
            } catch(err) {
                handlingFetchErr(err);
                callback(null);
            }
        }

    }
});