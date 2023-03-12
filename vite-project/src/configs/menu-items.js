export const menuLanding = {
    to: "/",
    title: "Dashboard",
    icon: "home",
    roles: ["admin", "viewer", "teknisi"]
};

export const menuMonitoring = {
    to: "/monitoring",
    title: "Monitoring RTU",
    icon: "airplay",
    roles: ["admin", "viewer", "teknisi"]
};

export const menuRtu = {
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
    to: "/pue",
    title: "Monitoring PUE",
    icon: "feather",
    roles: ["admin", "viewer", "teknisi"]
};

export const menuUser = {
    to: "/user",
    title: "Manajemen User",
    icon: "users",
    roles: ["admin"]
};

export default [
    { key: "landing", ...menuLanding },
    { key: "monitoring", ...menuMonitoring },
    { key: "rtu", ...menuRtu },
    { key: "gepee", ...menuGepee },
    { key: "pue", ...menuPue },
    { key: "user", ...menuUser }
];