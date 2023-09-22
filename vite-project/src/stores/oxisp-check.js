import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { createUrlParams } from "@/helpers/url";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

import { allowSampleData } from "@/configs/base";
import sampleOxispCheck from "@helpers/sample-data/oxisp-check/all";

export const useOxispCheckStore = defineStore("oxisp-check", {
    state: () => ({

        categories: []

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

        async fetchCategory(force = false, callback = null) {
            if(this.categories.length > 0 && !force) {
				callback && callback(true);
				return;
			}

            try {

                const response = await http.get("/oxisp-check/category", this.fetchHeader);
                
                if(Array.isArray(response.data.categories) && response.data.categories.length > 0) {
                    this.categories = response.data.categories;
                } else {
                    console.warn(response.data);
                }
                
                callback && callback(response.data?.success || false);

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData) {
                    const data = sampleOxispCheck;
                    if(Array.isArray(data.categories) && data.categories.length > 0) {
                        this.categories = data.categories;
                    }
                }
                
                callback && callback(false);
                    
            }
        },
        
        async getList(callback) {
            const urlParams = this.buildUrlParams("divre", "witel", "month", "year");
            try {

                const response = await http.get("/oxisp-check" + urlParams, this.fetchHeader);
                if(!response.data.check_data) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                if(Array.isArray(response.data.categories) && response.data.categories.length > 0) {
                    this.categories = response.data.categories;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData) {
                    const data = sampleOxispCheck;
                    if(Array.isArray(data.categories) && data.categories.length > 0) {
                        this.categories = data.categories;
                    }
                    callback({ success: false, status: err.response?.status, data });
                }
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        }

    }
});