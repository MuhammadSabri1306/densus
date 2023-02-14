import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";

export const useLocationStore = defineStore("location", {
    state: () => ({
        divreList: [],
        witelList: [],
        isLoadingWitel: false
    }),
    getters: {
        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        }
    },
    actions: {
        async fetchDivre(force = false, callback = null) {
            if(this.divreList.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            try {
                const response = await http.get("/monitoring/divre", this.fetchHeader);
                if(!response.data.divre) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.divreList = response.data.divre;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async fetchWitel(divreCode, callback = null) {
            this.isLoadingWitel = true;
            this.witelList = [];
            try {
                const response = await http.get("/monitoring/witel/" + divreCode, this.fetchHeader);
                if(!response.data.witel) {
                    console.warn(response.data);
                    this.isLoadingWitel = false;
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.witelList = response.data.witel;
                this.isLoadingWitel = false;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                this.isLoadingWitel = false;
                callback && callback({ success: false, status: err.response.status });
            }
        }
    }
});