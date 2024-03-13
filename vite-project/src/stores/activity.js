import { defineStore } from "pinia";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { getApiPath } from "@helpers/get-api-path";
import { backendUrl } from "@/configs/base";

import { allowSampleData } from "@/configs/base";
import sampleSchedule from "@helpers/sample-data/schedule";
// import sampleExecution from "@helpers/sample-data/execution";
import sampleExecution from "@helpers/sample-data/activity-execution";
import sampleCategory from "@helpers/sample-data/activity-category";
import sampleAvailableMonth from "@helpers/sample-data/available-month";
import sampleLocation from "@helpers/sample-data/location";
import sampleChart from "@helpers/sample-data/activity-chart";

const mapSchedule = item => {
    const createdAt = new Date(item.created_at);
    const updatedAt = new Date(item.updated_at);
    return { ...item, createdAt, updatedAt };
};

export const useActivityStore = defineStore("activity", {
    state: () => ({
        location: [],
        category: [],

        schedule: [],
        hasScheduleChanged: false,
        
        updatableTime: {
            schedule: {
                start: null,
                end: null
            },
            execution: {
                start: null,
                end: null
            },
        },

        execution: [],
        chart: null
    }),
    getters: {

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        },

        filters: () => {
            const viewStore = useViewStore();
            return viewStore.filters;
        },

        monthList: () => {
            const viewStore = useViewStore();
            return viewStore.monthList;
        },

        scheduleTable(state) {
            const schedule = state.userSchedule;
            const location = state.location;
            const category = state.category;
            const filterMonth = this.filters.month;
            const monthList = this.monthList;

            const table = location.map(locItem => {
                const col = monthList
                    .filter(monthItem => !filterMonth || monthItem.number === filterMonth)
                    .reduce((monthCol, monthItem) => {
                        for(let i=0; i<category.length; i++) {

                            const categoryItem = { number: i+1, ...category[i] };
                            const scheduleCol = schedule.find(scheItem => {
                                return scheItem.id_lokasi == locItem.id && (scheItem.createdAt.getMonth() + 1) == monthItem.number && scheItem.id_category == categoryItem.id;
                            });

                            monthCol.push({
                                month: monthItem,
                                category: categoryItem,
                                location: locItem,
                                schedule: scheduleCol || null
                            });

                        }
                        
                        return monthCol;
                    }, []);
                    // console.log(col);
                return { location: locItem, col };
            });
            // console.log(table);
            return table;
        },

        performanceExcelExportUrl() {
            const filters = this.filters;
            const url = getApiPath(backendUrl + "/export/excel/activity/performance", filters);
            return  url;
        }

    },
    actions: {
        
        async fetchLocation(force = false, callback = null) {
            // console.log(this.location);
            if(this.location.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            try {
                let url = "/activity/lokasi";
                if(this.filters.divre) {
                    url += "/" + this.filters.divre;

                    if(this.filters.witel)
                        url += "/" + this.filters.witel;
                }

                const response = await http.get(url, this.fetchHeader);
                if(!response.data.lokasi) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.location = response.data.lokasi;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.location = sampleLocation.lokasi;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },
        
        async fetchCategory(force = false, callback = null) {
            if(this.category.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}
            try {
                const response = await http.get("/activity/category", this.fetchHeader);
                if(!response.data.category) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.category = response.data.category;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.category = sampleCategory.category;
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async fetchSchedule(force = false, callback = null) {
            if(this.schedule.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            let url = "/activity/schedule";
            if(this.filters.divre) {
                url += "/?divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
                if(this.filters.month)
                    url += "&month=" + this.filters.month;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
                this.initUpdatableTime(response.data);
                if(!response.data.schedule) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                const schedule = response.data.schedule.map(mapSchedule);
                this.schedule = schedule;
                this.userSchedule = schedule;

                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData) {
                    const schedule = sampleSchedule.schedule.map(mapSchedule);
                    this.schedule = schedule;
                    this.userSchedule = schedule;
                }
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        setHasScheduleChanged(val) {
            this.hasScheduleChanged = val;
        },

        async saveSchedule(schedule, callback = null) {
            const body = { schedule };
            const url = getApiPath("/activity/schedule", this.filters);
            try {
                const response = await http.post(url, body, this.fetchHeader);
                this.hasScheduleChanged = false;
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },
        
        async saveScheduleV2(schedule, callback = null) {
            const body = { schedule };
            const url = getApiPath("/activity/schedule-v3", this.filters);
            try {
                const response = await http.post(url, body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async getPerformance(callback) {
            const url = getApiPath("/activity/performance", this.filters);
            let data = { month_list: [], category_list: [], performance: [] };
            try {
                const response = await http.get(url, this.fetchHeader);
                const success = response.data.success;
                const status = response.status;

                if(!response.data.success)
                    console.warn(response.data);
                else
                    data = response.data;

                callback({ success, status, data });

            } catch(err) {
                handlingFetchErr(err);
                if(allowSampleData)
                    data = sampleExecution;
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async fetchExecution(scheduleId, force = false, callback = null) {
            if(this.execution.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}
            try {
                const response = await http.get("/activity/execution/" + scheduleId, this.fetchHeader);
                if(!response.data.executionList) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.execution = response.data.executionList;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData)
                    this.execution = sampleExecution.executionList;
                    
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async getExecution(scheduleId, callback) {
            const data = { schedule: null, executionList: [] };
            try {
                const response = await http.get("/activity/execution/" + scheduleId, this.fetchHeader);
                this.initUpdatableTime(response.data);
                if(!response.data.executionList) {
                    console.warn(response.data);
                    callback({ success: false, status: response.status, data });
                    return;
                }

                data.executionList = response.data.executionList;
                data.schedule = response.data.schedule;
                callback({ success: true, status: response.status, data });
            } catch(err) {
                if(allowSampleData) {
                    data.executionList = sampleExecution.executionList;
                    data.schedule = sampleExecution.schedule;
                }
                
                handlingFetchErr(err);
                callback({ success: false, status: err.response?.status, data });
            }
        },

        async addExecution(schedulId, body, callback = null) {
            try {
                const response = await http.post("/activity/execution/" + schedulId, body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async updateExecution(execId, body, callback = null) {
            try {
                const response = await http.put("/activity/execution/" + execId, body, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async deleteExecution(execId, callback = null) {
            try {
                const response = await http.delete("/activity/execution/" + execId, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async approveExecution(execId, callback = null) {
            try {
                const response = await http.put(`/activity/execution/${ execId }/approve`, null, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async rejectExecution(execId, reject_description, callback = null) {
            try {
                const response = await http.put(`/activity/execution/${ execId }/reject`, { reject_description }, this.fetchHeader);
                if(!response.data.success) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response.status });
            }
        },

        async getDashboard() {
            if(!this.filters.month) {
                const viewStore = useViewStore();
                viewStore.setFilter({
                    month: new Date().getMonth() + 1
                });
            }

            let url = "/activity/dashboard/?month=" + this.filters.month;
            if(this.filters.divre)
                url += "&divre=" + this.filters.divre;
            if(this.filters.witel)
                url += "&witel=" + this.filters.witel;

            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.dashboard)
                    return response.data.dashboard;
        
                console.warn(response.data);
                return [];
        
            } catch(err) {
        
                handlingFetchErr(err);
                return {};
                
            }
        },

        async fetchChart(force = false, callback = null) {
            if(this.chart && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}
            try {
                const response = await http.get("/activity/chart", this.fetchHeader);
                if(!response.data.chart)
                    return console.warn(response.data);
                
                this.chart = response.data.chart;
                callback && callback({ success: true, status: 200 });
        
            } catch(err) {
                if(allowSampleData)
                    this.chart = sampleChart.chart;
                
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
                
            }
        },

        initUpdatableTime(data) {
            if(!data.updatable_time)
                return;
            if(data.updatable_time.schedule) {
                this.updatableTime.schedule.start = new Date(data.updatable_time.schedule.start);
                this.updatableTime.schedule.end = new Date(data.updatable_time.schedule.end);
            }
            if(data.updatable_time.execution) {
                this.updatableTime.execution.start = new Date(data.updatable_time.execution.start);
                this.updatableTime.execution.end = new Date(data.updatable_time.execution.end);
            }
        }

    }
});