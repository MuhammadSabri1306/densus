import { defineStore } from "pinia";
import { createHttpInstance } from "@/helpers/http-common/http";
import { useViewStore } from "@/stores/view";
import { handlingFetchErr } from "@/helpers/error-handler";
import { createUrlParams } from "@/helpers/url";

let http = null;

export const useNewosaseStore = defineStore("newosase", {
    actions: {

        useHttp() {
            if(!http) {
                http = createHttpInstance("https://newosase.telkom.co.id/api/v1", {
                    headers: { "Accept": "application/json" }
                });
            }
            return http;
        },

        async getMapViewRtu({ regionalId, witelId }, callback) {
            const params = {
                isArea: "hide",
                isChildren: "view",
            };

            if(regionalId) params.regional = regionalId;
            if(witelId) params.witel = witelId;

            const rtus = [];
            try {
                const http = this.useHttp();
                const response = await http.get("/parameter-service/mapview", { params });
                if(Array.isArray(response.data.result)) {
                    for(let i=0; i<response.data.result.length; i++) {
                        if(Array.isArray(response.data.result[i].witel)) {
                            for(let j=0; j<response.data.result[i].witel.length; j++) {
                                if(Array.isArray(response.data.result[i].witel[j].rtu)) {
                                    for(let k=0; k<response.data.result[i].witel[j].rtu.length; k++) {
                                        rtus.push(response.data.result[i].witel[j].rtu[k]);
                                    }
                                } else {
                                    let key = `response.data.result[${ i }].witel[${ j }]`;
                                    console.warn({ [key] : response.data.result[i] });
                                }
                            }
                        } else {
                            let key = `response.data.result[${ i }]`;
                            console.warn({ [key] : response.data.result[i] });
                        }
                    }
                } else {
                    console.warn({ "response.data.result": response.data.result });
                }
            } catch(err) {
                handlingFetchErr(err);
            } finally {
                callback(rtus);
            }
        },

        async getRtuPorts(rtuSname, callback) {
            try {
                if(!rtuSname)
                    throw new Error(`rtuSname is empty, given ${ rtuSname }`);
                const http = this.useHttp();
                const params = { searchRtuSname: rtuSname };
                const response = await http.get("/dashboard-service/dashboard/rtu/port-sensors", { params });
                if(Array.isArray(response.data?.result?.payload)) {
                    callback(response.data.result.payload);
                    return;
                }
                console.warn(response.data);
                callback([]);
            } catch(err) {
                console.error(err);
                callback([]);
            }
        },

    }
});