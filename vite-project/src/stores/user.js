import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { getCookie, setCookie, deleteCookie } from "@helpers/app-cookie";

export const useUserStore = defineStore("user", {
    state: () => ({
        id: null,
        name: null,
        role: null,
        level: null,
        location: null,
        token: null
    }),
    getters: {
        hasToken: state => state.token && state.token.length > 0,
        axiosAuthConfig() {
            if(!this.hasToken)
                return {};
            const token = this.token;
            const headers = { "Authorization": "Bearer " + token };
            return { headers };
        }
    },
    actions: {
        async login(body, callback = null) {
            try {
                const { is_ldap, username, password } = body;
                const url_get = `/login/${ is_ldap }/${ username }/${ password }`;
                const response = await http.get(url_get);

                // const response = await http.post("/login", body);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                const data = response.data.user;
                this.sync({
                    id: data.id,
                    name: data.name,
                    role: data.role,
                    level: data.level,
                    location: data.location || null,
                    token: data.token
                });
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                console.error(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

		logout(callback = null) {
			deleteCookie("user");
			this.id = null;
			this.name = null;
			this.role = null;
			this.level = null;
			this.location = null;
			this.token = null;
			callback && callback();
		},

        sync(params, updateCookie = true) {
			if(params.id && params.id !== undefined)
				this.id = params.id;
			if(params.name && params.name !== undefined)
				this.name = params.name;
            if(params.role && params.role !== undefined)
                this.role = params.role;
            if(params.level && params.level !== undefined)
                this.level = params.level;
            if(params.location && params.location !== undefined)
                this.location = params.location;
            if(params.token && params.token !== undefined)
                this.token = params.token;

			if(!updateCookie)
                return;
			setCookie("user", {
                id: this.id,
				name: this.name,
				role: this.role,
				level: this.level,
				location: this.location,
				token: this.token
			});
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
            if(data.role)
                params.role = data.role;
            if(data.level)
                params.level = data.level;
            if(data.location)
                params.location = data.location;
            if(data.token)
                params.token = data.token;
            this.sync(params, false);
		}
    }
});