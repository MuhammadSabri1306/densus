import { defineStore } from "pinia";

export const useViewStore = defineStore("view", {
    state: () => ({
        menuItems: [
            { key: "landing", to: "/", title: "Dashboard", icon: "home" },
            { key: "monitoring", to: "/", title: "Monitoring RTU", icon: "airplay" },
            {
                key: "rtu",
                title: "Master RTU",
                icon: "hard-drive",
                child: [
                    { key: "list", to: "/", title: "List RTU tersedia" },
                    { key: "add", to: "/", title: "Registrasi RTU Baru" },
                    { key: "backup", to: "/", title: "Buat Backup" }
                ]
            }
        ],
        menuActiveKey: "landing",
        menuExpanded: -1,
        hideSidebar: false
    }),
    getters: {
        menuActKeys: state => {
            const activeKey = state.menuActiveKey;
            if(activeKey.length < 1)
                return [state.menuItems[0].key];
            return activeKey.split("/");
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
        }
    }
});