import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

import { allowSampleData } from "@/configs/base";
import sampleGepeeReport from "@helpers/sample-data/gepee-report";

export const useGepeeReportStore = defineStore("gepee-report", {
    state: () => {
        const viewStore = useViewStore();
        return {
            monthList: viewStore.monthList
        };
    },
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            const divre = viewStore.filters.divre;
            const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const month = viewStore.filters.month;
            
            return { divre, witel, year, month };
        },

    },
    actions: {

        getUrlParams() {
            const filters = this.filters;

            const params = [];
            if(filters.divre)
                params.push("divre=" + filters.divre);
            if(filters.witel)
                params.push("witel=" + filters.witel);
            if(filters.month)
                params.push("month=" + filters.month);
            if(filters.year)
                params.push("year=" + filters.year);
            if(filters.idLocation)
                params.push("idLocation=" + filters.idLocation);

            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        async getReport(callback) {
            const urlParams = this.getUrlParams();
            const url = "/gepee-report-v2" + urlParams;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.gepee) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data: {} });
                    return;
                }

                callback({ success: true, status: response.status, data: response.data });

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback({ success: true, status: err.response?.status, data: sampleGepeeReport });
                else
                    callback({ success: false, status: err.response?.status, data: {} });
                    
            }
        }

    }
});