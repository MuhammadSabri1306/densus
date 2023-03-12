import { defineStore } from "pinia";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

import { allowSampleData } from "@/configs/base";
import sampleSchedule from "@helpers/sample-data/schedule";
import sampleExecution from "@helpers/sample-data/execution";
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
        availableMonth: new Date().getMonth() + 1,
        hasAvailableMonthFetched: false,
        userSchedule: [],
        hasScheduleChanged: false,
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
        
        async fetchAvailableMonth(force = false, callback = null) {
            if(this.hasAvailableMonthFetched && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}
            try {
                const response = await http.get("/activity/availablemonth");
                if(!response.data.month) {
                    console.warn(response.data);
                    callback && callback({ success: false, status: response.status });
                    return;
                }

                this.availableMonth = response.data.month;
                this.hasAvailableMonthFetched = true;
                callback && callback({ success: true, status: response.status });
            } catch(err) {
                if(allowSampleData) {
                    this.availableMonth = sampleAvailableMonth.month;
                    this.hasAvailableMonthFetched = true;
                }
                handlingFetchErr(err);
                callback && callback({ success: false, status: err.response?.status });
            }
        },

        async fetchSchedule(force = false, callback = null) {
            if(this.schedule.length > 0 && !force) {
				callback && callback({ success: true, status: 200 });
				return;
			}

            let url = "/activity/schedule/?ischecked=1";
            if(this.filters.divre) {
                url += "&divre=" + this.filters.divre;

                if(this.filters.witel)
                    url += "&witel=" + this.filters.witel;
            }

            try {
                const response = await http.get(url, this.fetchHeader);
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

        updateSheduleItem(id) {
            const index = this.userSchedule.findIndex(item => item.id == id);
            if(index < 0)
                return;
                
            const schedule = this.userSchedule;
            schedule[index] = schedule[index].value == 1 ? 0 : 1;
            this.userSchedule = schedule;

            if(!this.hasScheduleChanged)
                this.hasScheduleChanged = true;
        },

        addScheduleItem(categoryId, locationId) {
            const currDate = new Date();
            const iso = currDate.toISOString().match(/(\d{4}\-\d{2}\-\d{2})T(\d{2}:\d{2}:\d{2})/);
            const dateStr = iso[1] + " " + iso[2];

            const scheduleItem = {
                id_category: categoryId,
                id_lokasi: locationId,
                value: 1,
                created_at: dateStr,
                createdAt: currDate,
                updated_at: dateStr,
                updatedAt: currDate,
                is_enabled: 1
            };

            this.userSchedule = [...this.userSchedule, scheduleItem];
            if(!this.hasScheduleChanged)
                this.hasScheduleChanged = true;
        },

        removeScheduleItem(categoryId, locationId) {
            this.userSchedule = this.userSchedule.filter(item => {
                return item.id_category != categoryId != item.id_lokasi != locationId;
            });

            if(!this.hasScheduleChanged)
                this.hasScheduleChanged = true;
        },

        async saveSchedule(callback = null) {
            const divreCode = this.filters.divre;
            const schedule = this.userSchedule
                .filter(item => item.value == 1)
                .map(item => {
                    return `${ item.id_lokasi }&${ item.createdAt.getMonth() + 1 }&${ item.id_category }`;
                });

            const body = { schedule, divreCode };
            console.log(body);
            try {
                const response = await http.post("/activity/schedule", body, this.fetchHeader);
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

        async fetchExecution(scheduleId, force = false, callback = null) {
            if(this.schedule.length > 0 && !force) {
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

        async addExecution(schedulId, body, callback = null) {
            console.log(body);
            const { headers } = this.fetchHeader;
            headers["Content-Type"] = "multipart/form-data";
            try {
                const response = await http.post("/activity/execution/" + schedulId, body, { headers });
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
            console.log(body);
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
        }

    }
});