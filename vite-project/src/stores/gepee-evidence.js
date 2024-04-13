import { defineStore } from "pinia";
import http from "@/helpers/http-common";
import { handlingFetchErr } from "@/helpers/error-handler";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";

import { allowSampleData } from "@/configs/base";
import sampleCategory from "@/helpers/sample-data/gepee-evidence/category";
import sampleCategoryData from "@/helpers/sample-data/gepee-evidence/category-data";
import sampleDivre from "@/helpers/sample-data/gepee-evidence/divre";
import sampleWitel from "@/helpers/sample-data/gepee-evidence/witel";
import sampleLocationInfoNasional from "@/helpers/sample-data/gepee-evidence/location-info-nasional";
import sampleLocationInfoDivre from "@/helpers/sample-data/gepee-evidence/location-info-divre";
import sampleLocationInfoWitel from "@/helpers/sample-data/gepee-evidence/location-info-witel";
import sampleEvidenceList from "@/helpers/sample-data/gepee-evidence/evidence-list";
import sampleEvidence from "@/helpers/sample-data/gepee-evidence/evidence";

const getCurrentSemester = () => {
    const currMonth = new Date().getMonth() + 1;
    return Math.ceil(currMonth / 6);
};

export const useGepeeEvdStore = defineStore("gepee", {
    state: () => ({

        specialFilters: {
            semester: getCurrentSemester()
        },
        
        categoryList: []

    }),
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: state => {
            const viewStore = useViewStore();
            // const divre = viewStore.filters.divre;
            // const witel = viewStore.filters.witel;
            const year = viewStore.filters.year;
            const semester = state.specialFilters.semester;
            // const idLocation = state.specialFilters.idLocation;
            return { year, semester };
        }

    },
    actions: {

        setFilter(filter = {}) {
            const viewStore = useViewStore();

            if(filter.divre)
                viewStore.setFilter({ divre: filter.divre });
            if(filter.witel)
                viewStore.setFilter({ witel: filter.witel });
            if(filter.year)
                viewStore.setFilter({ year: filter.year });
            if(filter.semester)
                this.specialFilters.semester = filter.semester;
        },

        buildUrlParams(settedParams = {}) {
            const filters = this.filters;
            const params = [];

            if(settedParams.divre)
                params.push("divre=" + settedParams.divre)
            if(settedParams.witel)
                params.push("witel=" + settedParams.witel)
            if(settedParams.idLocation)
                params.push("idLocation=" + settedParams.idLocation)
            if(filters.semester || settedParams.semester)
                params.push("semester=" + (settedParams.semester || filters.semester))
            if(filters.year || settedParams.year)
                params.push("year=" + (settedParams.year || filters.year))
            return params.length < 1 ? "" : "/?" + params.join("&");
        },

        fetchCategory(force = false, callback = null) {
            if(this.categoryList.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            this.categoryList = sampleCategory.category;
            callback && callback({ success: true, status: 200 });
        },

        async getDivreList(callback) {
            const urlParams = this.buildUrlParams();
            try {

                const response = await http.get("/gepee-evidence/location/" + urlParams, this.fetchHeader);
                if(response.data.location) {
                    callback(response.data);
                    return;
                }

                console.warn(response.data);
                callback({});

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback(sampleDivre);
                else
                    callback({});

            }
        },

        async getWitelList(divreCode, callback) {
            const urlParams = this.buildUrlParams({ divre: divreCode });
            try {

                const response = await http.get("/gepee-evidence/location" + urlParams, this.fetchHeader);
                if(response.data.location) {
                    callback(response.data);
                    return;
                }

                console.warn(response.data);
                callback({});

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback(sampleWitel);
                else
                    callback({});

            }
        },

        async getLocationInfo(settedParams, callback) {
            const urlParams = this.buildUrlParams(settedParams);
            try {

                const response = await http.get("/gepee-evidence/location/info" + urlParams, this.fetchHeader);
                if(response.data.location) {
                    callback(response.data);
                    return;
                }

                console.warn(response.data);
                callback({});

            } catch(err) {

                handlingFetchErr(err);
                let data = {};
                if(allowSampleData && settedParams.witel)
                    data = sampleLocationInfoWitel;
                else if(allowSampleData && settedParams.divre)
                    data = sampleLocationInfoDivre;
                else if(allowSampleData)
                    data = sampleLocationInfoNasional;
                
                callback(data);

            }
        },

        async getCategoryData(witel, callback) {
            const urlParams = this.buildUrlParams({ witel });
            try {

                const response = await http.get("/gepee-evidence/category-data" + urlParams, this.fetchHeader);
                if(response.data.category_data) {
                    callback(response.data);
                    return;
                }

                console.warn(response.data);
                callback({});

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback(sampleCategoryData);
                else
                    callback({});

            }
        },

        async getEvidenceList(witel, idCategory, callback) {
            const urlParams = this.buildUrlParams({ witel });
            const url = `/gepee-evidence/evidence/category/${ idCategory + urlParams }`;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.evidence)
                    console.warn(response.data);
                callback(response.data);

            } catch(err) {

                handlingFetchErr(err);
                if(allowSampleData)
                    callback(sampleEvidenceList);
                else
                    callback({});

            }
        },

        async create(body, callback = null) {
            try {
                const response = await http.post("/gepee-evidence/evidence", body, this.fetchHeader);
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

        async update(id, body, callback = null) {
            try {
                const response = await http.put("/gepee-evidence/evidence/" + id, body, this.fetchHeader);
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

        async delete(id, callback = null) {
            try {
                const response = await http.delete("/gepee-evidence/evidence/" + id, this.fetchHeader);
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