import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { handlingFetchErr } from "@/helpers/error-handler";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";

// import { allowSampleData } from "@/configs/base";
// import samplePueTargetReport from "@/helpers/sample-data/pue-target/report";

export const useOxispStore = defineStore("oxisp", {
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

        getUrlParams(customParams = null, ...exclude) {
            const filters = this.filters;
            for(let key in customParams) {
                filters[key] = customParams[key];
            }

            const params = [];
            if(exclude.indexOf("divre") < 0 && filters.divre)
                params.push("divre=" + filters.divre);
            if(exclude.indexOf("witel") < 0 && filters.witel)
                params.push("witel=" + filters.witel);
            if(exclude.indexOf("year") < 0 && filters.year)
                params.push("year=" + filters.year);
            if(exclude.indexOf("month") < 0 && filters.month)
                params.push("month=" + filters.month);
            if(exclude.indexOf("idLocation") < 0 && filters.idLocation)
                params.push("idLocation=" + filters.idLocation);

            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        async getPerformance(callback) {
            const urlParams = this.getUrlParams();
            const url = "/oxisp/list" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.performance) {
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

        async getList(year, month, idLocation, callback) {
            const url = `/oxisp/list/${ year }/${ month }/${ idLocation }`;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.activity) {
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

        async create(year, month, body, callback = null) {
            const url = `/oxisp/${ year }/${ month }`;
            try {
                const response = await http.post(url, body, this.fetchHeader);
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
                const response = await http.put("/oxisp/" + id, body, this.fetchHeader);
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

        async approve(id, callback = null) {
            try {
                const response = await http.put("/oxisp/approve/" + id, null, this.fetchHeader);
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

        async reject(id, reject_descr, callback = null) {
            const body = { reject_descr };
            try {
                const response = await http.put("/oxisp/reject/" + id, body, this.fetchHeader);
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
                const response = await http.delete("/oxisp/" + id, this.fetchHeader);
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

        async getLocation(idLocation, callback) {
            const urlParams = this.getUrlParams({ idLocation });
            const url = "/oxisp/location" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.location) {
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

        async createLocation(body, callback = null) {
            try {
                const response = await http.post("/oxisp/location", body, this.fetchHeader);
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

        async updateLocation(idLocation, body, callback = null) {
            try {
                const response = await http.put("/oxisp/location/" + idLocation, body, this.fetchHeader);
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

        async deleteLocation(idLocation, callback = null) {
            try {
                const response = await http.delete("/oxisp/location/" + idLocation, this.fetchHeader);
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