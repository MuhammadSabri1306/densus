import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { useViewStore } from "@stores/view";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { createUrlParams } from "@/helpers/url";

export const useRtuStore = defineStore("rtu", {
    state: () => ({

        list: []

    }),
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        datatable: state => {
            return state.list.map((item, index) => {
                const no = index + 1;
                return { no, ...item };
            });
        },

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            
            return { divre, witel };
        },

    },

    actions: {

        async getDetail(rtuId, callback) {
            try {
                const response = await http.get("/rtu/" + rtuId, this.fetchHeader);
                if(!response.data.rtu) {
                    console.warn(response.data);
                    callback(null);
                    return;
                }

                callback(response.data.rtu);
            } catch(err) {
                handlingFetchErr(err);
                callback(null);
            }
        },

        async fetchList(force = false, callback = null) {
            if(this.list.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            const params = {};
            if(this.filters.divre)
                params.divre = this.filters.divre;
            if(this.filters.witel)
                params.witel = this.filters.witel;
            const urlParams = createUrlParams(params);

            try {
                const response = await http.get("/rtu?"+urlParams, this.fetchHeader);
                if(!response.data.rtu) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.list = response.data.rtu;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async create(body, callback = null) {
            try {
                const response = await http.post("/rtu", body, this.fetchHeader);
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
                const response = await http.put("/rtu/" + id, body, this.fetchHeader);
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
                const response = await http.delete("/rtu/" + id, this.fetchHeader);
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