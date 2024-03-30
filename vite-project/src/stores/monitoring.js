import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { createUrlParams } from "@/helpers/url";

import { allowSampleData } from "@/configs/base";
import sampleRtuDetail from "@helpers/sample-data/monitoring/rtu-detail";
import samplePueDetail from "@helpers/sample-data/monitoring/pue-detail";

export const useMonitoringStore = defineStore("monitoring", {
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

        async getRtuList(callback) {
            const params = {};
            if(this.filters.divre)
                params.divre = this.filters.divre;
            if(this.filters.witel)
                params.witel = this.filters.witel;
            const urlParams = createUrlParams(params);

            try {
                const response = await http.get("/monitoring/rtulist/v2?"+urlParams, this.fetchHeader);
                if(response.data.rtu) {
                    callback(response.data.rtu);
                    return;
                }
                console.warn(response.data);
                callback(response.data.rtu);
            } catch(err) {
                handlingFetchErr(err);
                callback([]);
            }
        },

        async getRtuDetail(rtuCode) {
            try {

                const response = await http.get("/monitoring/rtudetail/" + rtuCode, this.fetchHeader);
                if(response.data.rtu)
                    return response.data.rtu;
        
                console.warn(response.data);
                return {};
        
            } catch(err) {
                
                handlingFetchErr(err);
                return allowSampleData ? sampleRtuDetail.rtu : {};
                
            }
        },

        async getKwhReal(rtuCode) {
            try {

                const response = await http.get("/monitoring/kwhreal/" + rtuCode, this.fetchHeader);
                if(response.data.kwh_real)
                    return response.data.kwh_real;
        
                console.warn(response.data);
                return 0;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return 0;
                
            }
        },

        async getKwhToday(rtuCode) {
            try {

                const response = await http.get("/monitoring/kwhtoday/" + rtuCode, this.fetchHeader);
                if(response.data.kwh_today && response.data.kwh_today.kwh_value)
                    return response.data.kwh_today.kwh_value;
        
                console.warn(response.data);
                return 0;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return 0;
                
            }
        },

        async getKwhTotal(rtuCode) {
            try {

                const response = await http.get("/monitoring/kwhtotal/" + rtuCode, this.fetchHeader);
                if(response.data.kwh_total && response.data.kwh_total.kwh_value)
                    return response.data.kwh_total.kwh_value;
        
                console.warn(response.data);
                return 0;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return 0;
                
            }
        },

        async getTableData(rtuCode) {
            const emptyData = { chart: {}, table: [] };
            try {

                const response = await http.get("/monitoring/tabledata/" + rtuCode, this.fetchHeader);
                if(response.data.tabledata)
                    return response.data.tabledata;
        
                console.warn(response.data);
                return emptyData;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return emptyData;
                
            }
        },

        async getDegTableData(rtuCode) {
            const emptyData = { chart: {}, table: [] };
            try {

                const response = await http.get("/monitoring/degtabledata/" + rtuCode, this.fetchHeader);
                if(response.data.degtabledata)
                    return response.data.degtabledata;
        
                console.warn(response.data);
                return emptyData;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return emptyData;
                
            }
        },

        async getBbmCost(rtuCode) {
            try {

                const response = await http.get("/monitoring/costbbm/" + rtuCode, this.fetchHeader);
                if(response.data.bbm_cost)
                    return Number(response.data.bbm_cost);
        
                console.warn(response.data);
                return 0;
        
            } catch(err) {
        
                handlingFetchErr(err);
                return 0;
                
            }
        },

        async getChartDataDaily(rtuCode) {
            try {

                const response = await http.get("/monitoring/chartdatadaily/" + rtuCode, this.fetchHeader);
                if(response.data.chartdata_daily)
                    return response.data.chartdata_daily;
        
                console.warn(response.data);
                return [];
        
            } catch(err) {
        
                handlingFetchErr(err);
                return [];
                
            }
        },

        async getSavingPercent(rtuCode) {
            try {

                const response = await http.get("/monitoring/savingpercent/" + rtuCode, this.fetchHeader);
                if(response.data.savingpercent)
                    return response.data.savingpercent;
        
                console.warn(response.data);
                return {};
        
            } catch(err) {
        
                handlingFetchErr(err);
                return {};
                
            }
        },

        async getRtuListPue(filter = {}) {
            let url = "/monitoring/pue";
            if(filter.divre) {
                url += "?divre=" + filter.divre;
                if(filter.witel)
                    url += "&witel=" + filter.witel;
            }
            
            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.rtu)
                    return response.data.rtu;
        
                console.warn(response.data);
                return [];
        
            } catch(err) {
        
                handlingFetchErr(err);
                return [];
                
            }
        },

        async getPueDetail(rtuCode, callback) {
            try {

                const response = await http.get("/monitoring/pue/" + rtuCode, this.fetchHeader);
                if(response.data.pue) {
                    callback(response.data.pue);
                    return;
                }
        
                console.warn(response.data);
                callback({});
        
            } catch(err) {
        
                handlingFetchErr(err);
                if(allowSampleData)
                    callback(samplePueDetail.pue);
                else callback({});
                
            }
        }

    }
});