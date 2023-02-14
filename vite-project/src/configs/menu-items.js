export default [
    { key: "landing", to: "/", title: "Dashboard", icon: "home", roles: ["admin", "viewer", "teknisi"] },
    { key: "monitoring", to: "/monitoring", title: "Monitoring RTU", icon: "airplay", roles: ["admin", "viewer", "teknisi"] },
    {
        key: "rtu",
        title: "Master RTU",
        icon: "hard-drive",
        child: [
            { key: "list", to: "/rtu", title: "List RTU tersedia" },
            { key: "add", to: "/rtu/add", title: "Registrasi RTU Baru" },
            { key: "backup", to: "/", title: "Buat Backup" }
        ],
        roles: ["admin", "viewer", "teknisi"]
    },
    { key: "user", to: "/user", title: "Manajemen User", icon: "users", roles: ["admin"] }
];