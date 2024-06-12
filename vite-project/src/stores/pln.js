import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { handlingFetchErr } from "@/helpers/error-handler";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";

// import { allowSampleData } from "@/configs/base";
// import samplePlnBill from "@/helpers/sample-data/pln/bill";
// import samplePlnBillOnLocation from "@/helpers/sample-data/pln/bill-on-location";
// import samplePlnParams from "@/helpers/sample-data/pln/params";

export const usePlnStore = defineStore("pln", {
    state: () => null,
    getters: {
        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            return viewStore.filters;
        }
    },
    actions: {

        async createBillLocation(body, callback = null) {
            try {

                const response = await http.post("/monitoring/pln/bill/location", body, this.fetchHeader);
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

        async getBill(callback) {
            let url = "/monitoring/pln/bill";
            if(this.filters.divre) {
                url += "/?divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pln_bill) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.pln_bill
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnBill.pln_bill;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async getBillOnLocation(locationId, callback) {
            try {
                const response = await http.get("/pln/billing/?location=" + locationId, this.fetchHeader);
                if(!response.data.pln_bill) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.pln_bill
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnBillOnLocation.pln_bill;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async createBill(body, callback = null) {
            try {

                const response = await http.post("/monitoring/pln/bill", body, this.fetchHeader);
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

        async updateBill(id, body, callback = null) {
            try {

                const response = await http.put("/monitoring/pln/bill/" + id, body, this.fetchHeader);
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

        async getParams(callback) {
            let url = "/monitoring/pln/params";
            if(this.filters.divre) {
                url += "/?divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.pln_params) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.pln_params
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnParams.pln_params;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async createParams(body, callback = null) {
            try {

                const response = await http.post("/monitoring/pln/params", body, this.fetchHeader);
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

        async updateParams(id, body, callback = null) {
            try {

                const response = await http.put("/monitoring/pln/params/" + id, body, this.fetchHeader);
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