import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { backendUrl } from "@/configs/base";
import { createUrlParams } from "@/helpers/url";

import { allowSampleData } from "@/configs/base";
import samplePueOfflineByLocation from "@helpers/sample-data/pue/offline_by_location";
import sampleIkeList from "@helpers/sample-data/ike/ike-list";

export const useIkeStore = defineStore("ike", {
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

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            
            return { divre, witel, year, month };
        },

        monthList: () => {
            const viewStore = useViewStore();
            return viewStore.monthList;
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

        async getData(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "month", "year");
            try {
                const response = await http.get("/ike" + urlParams, this.fetchHeader);
                if(!response.data.ike_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });
            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleIkeList });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
            }
        },

        async getLocationData(idLocation, callback) {
            const urlParams = this.buildUrlParams("year", "month");
            try {
                const response = await http.get("ike/" + idLocation + urlParams, this.fetchHeader);
                if(!response.data.ike) {
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

        async create(body, callback = null) {
            try {
                const response = await http.post("/ike", body, this.fetchHeader);
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

        async update(idLocation, body, callback = null) {
            try {
                const response = await http.put("/ike/" + idLocation, body, this.fetchHeader);
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

        async delete(idLocation, callback = null) {
            try {
                const response = await http.delete("/ike/" + idLocation, this.fetchHeader);
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

    }
});