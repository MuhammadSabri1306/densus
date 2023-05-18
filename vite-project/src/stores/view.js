import { defineStore } from "pinia";
import { useUserStore } from "@stores/user";
import http from "@helpers/http-common";
import { handlingFetchErr } from "@helpers/error-handler";
import menuItems from "@/configs/menu-items";

import { allowSampleData } from "@/configs/base";
import sampleDivre from "@helpers/sample-data/divre";
import sampleWitelByDivre from "@helpers/sample-data/witel-by-divre";
import sampleWitel from "@helpers/sample-data/witel";

const monthList = ["Januari", "Februari", "Maret",
    "April", "Mei", "Juni", "Juli", "Agustus",
    "September", "Oktober", "November", "Desember"]
    .map((name, index) => {
        const number = index + 1;
        return { number, name };
    });

export const useViewStore = defineStore("view", {
    state: () => ({
        menuItems,
        menuActiveKey: "landing",
        menuExpanded: -1,
        hideSidebar: false,
        toast: null,
        monthList,
        filters: {
            divre: null,
            witel: null,
            year: null,
            month: null,
            quarter: null
        }
    }),
    getters: {
        
        menuActKeys: state => {
            const activeKey = state.menuActiveKey;
            if(activeKey.length < 1)
                return [state.menuItems[0].key];
            return activeKey.split(",");
        },

        fetchHeader: () => {
            const userStore = useUserStore();
            return userStore.axiosAuthConfig;
        }

    },
    actions: {
        setActiveMenu(...menuKeys) {
            this.menuActiveKey = menuKeys.join("/");
            this.menuExpanded = -1;
        },

        setMenuExpanded(menuIndex) {
            if(menuIndex <-1 || menuIndex > this.menuItems.length)
                return;
            this.menuExpanded = menuIndex;
        },

        toggleSidebarVisibility(show = null) {
            if(show === null) {
                this.hideSidebar = !this.hideSidebar;
                return;
            }
            this.hideSidebar = !show;
        },

        resetSidebarVisibility() {
            const bodyWidth = document.body.offsetWidth;
            const showSidebar = bodyWidth <= 991;
            this.hideSidebar = !showSidebar;
        },

        showToast(title, message, success) {
			this.toast = { title, message, success };
		},

        setFilter(filter = {}) {
            if(filter.divre !== undefined)
                this.filters.divre = filter.divre;
            if(filter.witel !== undefined)
                this.filters.witel = filter.witel;
            if(filter.year !== undefined)
                this.filters.year = Number(filter.year);
            if(filter.month !== undefined)
                this.filters.month = Number(filter.month);
            if(filter.quarter !== undefined)
                this.filters.quarter = Number(filter.quarter);
        },

        async getDivre(locationKey = "basic") {
            const urlKey = locationKey == "gepee" ? "/gepee" : "";
            const url = `/location${ urlKey }/divre`;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.divre)
                    return response.data.divre;
        
                console.warn(response.data);
                return [];
            } catch(err) {
                handlingFetchErr(err);
                return allowSampleData ? sampleDivre.divre : [];
            }
        },

        async getWitel(witelCode = null, locationKey = "basic") {
            witelCode = witelCode ? "/" + witelCode : "";
            const urlKey = locationKey == "gepee" ? "/gepee" : "";
            const url = `/location${ urlKey }/witel${ witelCode }`;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.witel)
                    return response.data.witel;
        
                console.warn(response.data);
                return [];
        
            } catch(err) {
        
                handlingFetchErr(err);
                return allowSampleData ? sampleWitel.witel : [];
                
            }
        },

        async getWitelByDivre(divreCode, locationKey = "basic") {
            const urlKey = locationKey == "gepee" ? "/gepee" : "";
            const url = `/location${ urlKey }/divre/${ divreCode }/witel`;
            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.witel)
                    return response.data.witel;
        
                console.warn(response.data);
                return {};
        
            } catch(err) {
        
                handlingFetchErr(err);
                return allowSampleData ? sampleWitelByDivre.witel : [];
                
            }
        },

        async getSto(filter = {}, locationKey = "basic") {
            const urlKey = locationKey == "gepee" ? "/gepee" : "";
            let url = `/location${ urlKey }/sto`;

            if(filter.divre)
                url += "/" + filter.divre;
            if(filter.witel)
                url += "/" + filter.witel;

            try {

                const response = await http.get(url, this.fetchHeader);
                if(response.data.sto)
                    return response.data.sto;
        
                console.warn(response.data);
                return [];
        
            } catch(err) {
        
                handlingFetchErr(err);
                return [];
                
            }
        },
    }
});