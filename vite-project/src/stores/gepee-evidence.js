import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";

import { allowSampleData } from "@/configs/base";
import sampleCategory from "@helpers/sample-data/gepee-evidence/category";
import sampleCategoryData from "@helpers/sample-data/gepee-evidence/category-data";
import sampleDivre from "@helpers/sample-data/gepee-evidence/divre";
import sampleWitel from "@helpers/sample-data/gepee-evidence/witel";
import sampleLocationInfoNasional from "@helpers/sample-data/gepee-evidence/location-info-nasional";
import sampleLocationInfoDivre from "@helpers/sample-data/gepee-evidence/location-info-divre";
import sampleLocationInfoWitel from "@helpers/sample-data/gepee-evidence/location-info-witel";
import sampleEvidenceList from "@helpers/sample-data/gepee-evidence/evidence-list";
import sampleEvidence from "@helpers/sample-data/gepee-evidence/evidence";

const getCurrentSemester = () => {
    const currMonth = new Date().getMonth() + 1;
    return Math.ceil(currMonth / 6);
};

export const useGepeeEvdStore = defineStore("gepee", {
    state: () => ({

        filters: {
            semester: getCurrentSemester(),
            year: new Date().getFullYear()
        },
        lastFetch: null,
        categoryList: []

    }),
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        urlParams: state => {
            return (settedFilter = {}) => {
                const params = [];
                if(settedFilter.divre)
                    params.push("divre=" + settedFilter.divre)
                if(settedFilter.witel)
                    params.push("witel=" + settedFilter.witel)
                if(settedFilter.idLocation)
                    params.push("idLocation=" + settedFilter.idLocation)
                if(state.filters.semester)
                    params.push("semester=" + state.filters.semester)
                if(state.filters.year)
                    params.push("year=" + state.filters.year)
                return params.length < 1 ? "" : "/?" + params.join("&");
            };
        }

    },
    actions: {

        setFilter(filter = {}) {
            if(filter.semester)
                this.filters.semester = filter.semester;
            if(filter.year)
                this.filters.year = filter.year;
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
            const urlParams = this.urlParams();
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
            const urlParams = this.urlParams({ divre: divreCode });
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

        async getLocationInfo(settedFilter, callback) {
            const urlParams = this.urlParams(settedFilter);
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
                if(allowSampleData && settedFilter.witel)
                    data = sampleLocationInfoWitel;
                else if(allowSampleData && settedFilter.divre)
                    data = sampleLocationInfoDivre;
                else if(allowSampleData)
                    data = sampleLocationInfoNasional;
                
                callback(data);

            }
        },

        async getCategoryData(witel, callback) {
            const urlParams = this.urlParams({ witel });
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
            const urlParams = this.urlParams({ witel });
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