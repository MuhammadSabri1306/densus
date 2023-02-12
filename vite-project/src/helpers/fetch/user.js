import http from "@helpers/http-common";
import { useUserStore } from "@stores/user";

const getAuthConfig = () => {
    const userStore = useUserStore();
    if(!userStore.token)
        return {};

    const header = { "Authorization": "Bearer " + userStore.token };
    return { header };
};

export const fetchUserList = () => http.get("/user", getAuthConfig());

export const fetchUserUpdate = (id, body) => {
    if(body.is_ldap)
        body.is_ldap = body.is_ldap ? 1 : 0;
    if(body.is_active)
        body.is_active = body.is_active ? 1 : 0;
    return http.put("/user/" + id, body, getAuthConfig());
};

export const fetchUserDelete = id => http.delete("/user/" + id, getAuthConfig());