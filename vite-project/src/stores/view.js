import { defineStore } from "pinia";
import menuItems from "@/configs/menu-items";

export const useViewStore = defineStore("view", {
    state: () => ({
        menuItems,
        menuActiveKey: "landing",
        menuExpanded: -1,
        hideSidebar: false,
        toast: null
    }),
    getters: {
        menuActKeys: state => {
            const activeKey = state.menuActiveKey;
            if(activeKey.length < 1)
                return [state.menuItems[0].key];
            return activeKey.split(",");
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
		}
    }
});