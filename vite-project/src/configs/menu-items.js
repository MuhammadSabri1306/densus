
export const menuLanding = {
    key: "landing",
    to: "/",
    title: "Dashboard",
    icon: "home",
    roles: ["admin", "viewer", "teknisi"]
};

export const menuMonitoring = {
    key: "monitoring",
    to: "/monitoring",
    title: "Monitoring RTU",
    icon: "airplay",
    roles: ["admin", "viewer", "teknisi"]
};

export const menuMonitoringEnergy = {
    key: "energy",
    to: "/monitoring-v2",
    title: "Monitoring Energy",
    icon: "airplay",
    roles: ["admin", "viewer", "teknisi"],
    isDev: true
};

export const menuRtu = {
    key: "rtu",
    title: "Master RTU",
    icon: "hard-drive",
    child: [
        { key: "list", to: "/rtu", title: "List RTU tersedia" },
        { key: "add", to: "/rtu/add", title: "Registrasi RTU Baru" },
        { key: "backup", to: "/", title: "Buat Backup" }
    ],
    roles: ["admin", "viewer", "teknisi"]
};

export const menuGepee = {
    key: "gepee",
    title: "Gepee Activity",
    icon: "zap",
    child: [
        { key: "dashboard", to: "/gepee", title: "Dashboard" },
        { key: "schedule", to: "/gepee/schedule", title: "Penjadwalan", roles: ["admin"] },
        { key: "exec", to: "/gepee/exec", title: "Pelaksanaan" }
    ],
    roles: ["admin", "viewer", "teknisi"]
};

export const menuPue = {
    key: "pue",
    title: "Monitoring PUE & IKE",
    icon: "feather",
    child: [
        { key: "online", to: "/pue/online", title: "PUE Online" },
        { key: "offline", to: "/pue/offline", title: "PUE Offline" },
        { key: "ike", to: "#", title: "IKE" }
    ],
    roles: ["admin", "viewer", "teknisi"]
};

export const menuUser = {
    key: "user",
    to: "/user",
    title: "Manajemen User",
    icon: "users",
    roles: ["admin"]
};

export const menuGepeeEvidence = {
    key: "gepee_evidence",
    to: "/gepee-evidence",
    title: "Gepee Evidence",
    icon: "award",
    roles: ["admin", "viewer", "teknisi"]
};

export default (() => {
    const menu = [ menuLanding, menuPue, menuMonitoring, menuMonitoringEnergy, menuRtu, menuGepee, menuGepeeEvidence, menuUser ];
    return menu.map(item => {

        const menuItem = { name: item.key, ...item };
        if(!item.child)
            return menuItem;
        
        menuItem.child = item.child.map(cItem => {
            return { name: `${ item.key }-${ cItem.key }`, ...cItem };
        });

        return menuItem;
    });
})();