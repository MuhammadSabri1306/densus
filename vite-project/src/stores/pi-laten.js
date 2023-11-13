import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { createUrlParams } from "@/helpers/url";

import { allowSampleData } from "@/configs/base";
import getSampleData from "@helpers/sample-data";

export const usePiLatenStore = defineStore("pi-laten", {
    state: () => {
        const viewStore = useViewStore();
        return {
            monthList: viewStore.monthList,
            locationMode: "gepee"
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

        changeLocationMode(mode) {
            this.locationMode = mode;
        },

        async getPi(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "year", "month");
            const url = this.locationMode == "gepee" ? "/pi-laten/gepee" : "/pi-laten/amc";
            try {

                const response = await http.get(url + urlParams, this.fetchHeader);
                if(!response.data.list) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData) {
                    const sampleData = await getSampleData("pi-laten-gepee", {});
                    callback({ success: true, status: err.response?.status, data: sampleData });
                } else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        },

        async getPiV2(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "year", "month");
            try {

                const response = await http.get("/pi-laten/gepee/v2" + urlParams, this.fetchHeader);
                if(!response.data.witel_list) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData) {
                    const sampleData = await getSampleData("pi-laten-gepee-v2", {});
                    callback({ success: true, status: err.response?.status, data: sampleData });
                } else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        }

    }
});