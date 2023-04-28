import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

// import { allowSampleData } from "@/configs/base";
// import samplePlnBillingArea from "@helpers/sample-data/pln/billing-area";
// import samplePlnBilling from "@helpers/sample-data/pln/billing";

export const useFuelStore = defineStore("fuel", {
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

        async createInvoiceLocation(body, callback = null) {
            try {

                const response = await http.post("/monitoring/fuel/invoice/location", body, this.fetchHeader);
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

        async getInvoice(callback) {
            let url = "/monitoring/fuel/invoice";
            if(this.filters.divre) {
                url += "/?divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.fuel_invoice) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.fuel_invoice
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnBill.fuel_invoice;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async getInvoiceOnLocation(locationId, callback) {
            try {
                const response = await http.get("/pln/billing/?location=" + locationId, this.fetchHeader);
                if(!response.data.fuel_invoice) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.fuel_invoice
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnBillOnLocation.fuel_invoice;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async createInvoice(body, callback = null) {
            try {

                const response = await http.post("/monitoring/fuel/invoice", body, this.fetchHeader);
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

        async updateInvoice(id, body, callback = null) {
            try {

                const response = await http.put("/monitoring/fuel/invoice/" + id, body, this.fetchHeader);
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
            let url = "/monitoring/fuel/params";
            if(this.filters.divre) {
                url += "/?divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
                if(!response.data.fuel_params) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: [] });
                    return;
                }

                callback({
                    success: true,
                    status: response.status,
                    data: response.data.fuel_params
                });
            } catch(err) {
                handlingFetchErr(err);
                let data = [];
                // if(allowSampleData)
                //     data = samplePlnParams.fuel_params;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async createParams(body, callback = null) {
            try {

                const response = await http.post("/monitoring/fuel/params", body, this.fetchHeader);
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

                const response = await http.put("/monitoring/fuel/params/" + id, body, this.fetchHeader);
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