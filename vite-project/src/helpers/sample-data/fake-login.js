
const sampleUserResponse = {
    id: 1,
    username: "dev123",
    name: "Muhammad Sabri",
    role: "admin",
    level: "nasional",
    location: null,
    locationId: null,
    token: "sdasa"
};

export default ({ is_ldap, username, password }) => {
    if(!is_ldap && username == "test" && password == "test123")
        return sampleUserResponse;
    return null;
};