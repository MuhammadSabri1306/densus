import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
/* 
const getAuthConfig = () => {
    const userStore = useUserStore();
    if(!userStore.token)
        return {};

    const header = { "Authorization": "Bearer " + userStore.token };
    return { header };
}; */

export const useListUserStore = defineStore("listUser", {
    state: () => ({
        list: []
    }),
    getters: {
        datatable: state => {
            return state.list.map((item, index) => {
                const no = index + 1;
                return { no, ...item };
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
				callback && callback();
				return;
			}

            try {
                console.log(this.fetchHeader);
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
                callback && callback({ success: false, status: err.status });
            }
        },

        async create(body, callback = null) {
            body.is_ldap = body.is_ldap ? 1 : 0;
            body.is_active = body.is_active ? 1 : 0;
            try {
                const response = await http.post("/user/", body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.status });
            }
        },

        async update(id, body, callback = null) {
            if(body.is_ldap)
                body.is_ldap = body.is_ldap ? 1 : 0;
            if(body.is_active)
                body.is_active = body.is_active ? 1 : 0;
            try {
                const response = await http.put("/user/" + id, body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.status });
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
                callback && callback({ success: false, status: err.status });
            }
        }
    }
});