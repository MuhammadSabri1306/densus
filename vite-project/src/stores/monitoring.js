import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";

export const useMonitoringStore = defineStore("monitoring", {
    state: () => null,
    getters: {
        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        }
    },
    actions: {

        async getRtuDetail(rtuCode) {
            try {

                const response = await http.get("/monitoring/rtudetail/" + rtuCode, this.fetchHeader);
                if(response.data.rtu)
                    return response.data.rtu;
        
                console.warn(response.data);
                return {};
        
            } catch(err) {
        
                handlingFetchErr(err);
                return {};
                
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
                    return response.data.bbm_cost;
        
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
        }

    }
});