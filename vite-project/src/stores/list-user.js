import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { handlingFetchErr } from "@/helpers/error-handler";
import { useUserStore } from "@/stores/user";

export const useListUserStore = defineStore("listUser", {
    state: () => ({
        list: []
    }),
    getters: {
        datatable: state => {
            const list = state.list;
            const userStore = useUserStore();

            return list.map((item, index) => {
                const no = index + 1;
                const isReadonly = userStore.role == item.role && item.role != "admin";

                return { no, ...item, isReadonly };
            });
        },
        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        }
    },
    actions: {
        async fetchList(force = false, callback = null) {
            if(this.list.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            try {
                const response = await http.get("/user", this.fetchHeader);
                if(!response.data.user) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.list = response.data.user;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async create(body, callback = null) {
            body.is_ldap = body.is_ldap ? 1 : 0;
            try {
                const response = await http.post("/user", body, this.fetchHeader);
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
            if(body.is_active !== undefined)
                body.is_active = body.is_active ? 1 : 0;
            try {
                const response = await http.put(`/user/${ id }/general`, body, this.fetchHeader);
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

        async updateActive(id, value, callback = null) {
            const body = { is_active: value ? 1 : 0 };
            try {
                const response = await http.put(`/user/${ id }/active`, body, this.fetchHeader);
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
                const response = await http.delete("/user/" + id, this.fetchHeader);
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