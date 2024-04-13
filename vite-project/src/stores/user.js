import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { getCookie, setCookie, deleteCookie } from "@/helpers/app-cookie";
import { useListUserStore } from "@/stores/list-user";
import { useRtuStore } from "@/stores/rtu";
import { handlingFetchErr } from "@/helpers/error-handler";

import { allowSampleData } from "@/configs/base";
import fakeLogin from "@/helpers/sample-data/fake-login";

export const useUserStore = defineStore("user", {
    state: () => ({
        id: null,
        name: null,
        username: null,
        role: null,
        level: null,
        location: null,
        locationId: null,
        token: null,
        userData: {}
    }),
    getters: {
        hasToken: state => state.token && state.token.length > 0,
        axiosAuthConfig() {
            if(!this.hasToken)
                return {};
            const token = this.token;
            const headers = { "Authorization": "Bearer " + token };
            return { headers };
        },
        axiosUploadConfig(useToken = true) {
            const headers = { "Content-Type": "multipart/form-data" };
            if(!this.hasToken || !useToken)
                return { headers };
                
            const token = this.token;
            headers["Authorization"] = "Bearer " + token;
            return { headers };
        }
    },
    actions: {
        async login(body, callback = null) {
            try {
                const response = await http.post("/login", body);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                const data = response.data.user;
                this.sync({
                    id: data.id,
                    name: data.name,
                    username: data.username,
                    role: data.role,
                    level: data.level,
                    location: data.location || null,
                    locationId: data.locationId || null,
                    token: data.token
                });
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData) {
                    const data = fakeLogin(body);
                    if(data) {
                        this.sync(data);
                        callback && callback({ success: true, status: err.response?.status });
                    }
                }

                console.error(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

		logout(callback = null) {
            // http.get("/logout", this.axiosAuthConfig);
            
			deleteCookie("user");
            this.$reset();

            const listUserStore = useListUserStore();
            listUserStore.$reset();

            const rtuStore = useRtuStore();
            rtuStore.$reset();

			callback && callback();
		},

        sync(params, updateCookie = true) {
			if(params.id && params.id !== undefined)
				this.id = params.id;
			if(params.name && params.name !== undefined)
				this.name = params.name;
			if(params.username && params.username !== undefined)
				this.username = params.username;
            if(params.role && params.role !== undefined)
                this.role = params.role;
            if(params.level && params.level !== undefined)
                this.level = params.level;
            if(params.location && params.location !== undefined)
                this.location = params.location;
            if(params.locationId && params.locationId !== undefined)
                this.locationId = params.locationId;
            if(params.token && params.token !== undefined)
                this.token = params.token;

			if(!updateCookie)
                return;
			setCookie("user", {
                id: this.id,
				name: this.name,
				username: this.username,
				role: this.role,
				level: this.level,
				location: this.location,
				locationId: this.locationId,
				token: this.token
			}, 2);
		},

		readUserCookie() {
			const data = getCookie("user");
            if(!data || data === undefined)
                return;

			let params = {};
            if(data.id)
                params.id = data.id;
            if(data.name)
                params.name = data.name;
            if(data.username)
                params.username = data.username;
            if(data.role)
                params.role = data.role;
            if(data.level)
                params.level = data.level;
            if(data.location)
                params.location = data.location;
            if(data.locationId)
                params.locationId = data.locationId;
            if(data.token)
                params.token = data.token;
            this.sync(params, false);
		},

        async getDivre(callback = null) {
            const data = { success: true, status: 200 };
            if(this.level == "nasional")
                data.divre = null;
            else if(this.level == "divre")
                data.divre = this.locationId;
            else {
                try {
                    const response = await http.get("/monitoring/location/" + this.locationId, this.axiosAuthConfig);
                    if(!response.data.location) {
                        console.warn(response.data);
                        data.success = false;
                        data.status = response.status;
                    }
                    const location = response.data.location;
                    if(location.divre_code) {
                        data.divre = location.divre_code;
                    }
                } catch(err) {
                    handlingFetchErr(err);
                    data.success = false;
                    data.status = err.response.status;
                }
            }
            callback && callback(data);
        },

        async getLocation(callback = null) {
            const data = { success: true, status: 200 };
            if(this.level == "nasional") {
                
                data.location = {
                    divre_code: null,
                    divre_name: null,
                    witel_code: null,
                    witel_name: null
                };

            } else if(this.level == "divre") {
                
                data.location = {
                    divre_code: this.locationId,
                    divre_name: this.location,
                    witel_code: null,
                    witel_name: null
                };
                
            } else {
                try {
                    const response = await http.get("/monitoring/location/" + this.locationId, this.axiosAuthConfig);
                    if(!response.data.location) {

                        console.warn(response.data);
                        data.success = false;
                        data.status = response.status;

                    } else {

                        data.location = response.data.location;
                        
                    }
                } catch(err) {
                    handlingFetchErr(err);
                    data.success = false;
                    data.status = err.response.status;
                }
            }
            callback && callback(data);
        },

        async updatePassword(body, callback = null) {
            try {
                const response = await http.put("/change_password", body, this.axiosAuthConfig);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                const data = response.data.user;
                this.sync({
                    id: data.id,
                    name: data.name,
                    username: data.username,
                    role: data.role,
                    level: data.level,
                    location: data.location || null,
                    locationId: data.locationId || null,
                    token: data.token
                });
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async fetchUserData(force = false, callback = null) {
            if(this.userData.id && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            try {
                console.log(this.axiosAuthConfig);
                const response = await http.get("/profile", this.axiosAuthConfig);
                if(!response.data.user) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.userData = response.data.user;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        }
    }
});