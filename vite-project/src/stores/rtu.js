import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";

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
        }
    },
    actions: {
        async fetchList(force = false, callback = null) {
            if(this.list.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            try {
                const response = await http.get("/rtu", this.fetchHeader);
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
                callback && callback({ success: false, status: err.response.status });
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
                callback && callback({ success: false, status: err.response.status });
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
                callback && callback({ success: false, status: err.response.status });
            }
        }
    }
});