import axios from "axios";
const http = axios.create({
	baseURL: "https://newosase.telkom.co.id/api/v1"
});

export const testApi = async () => {
    console.log("Start Test NewOsase");
    try {
        const response = await http.get("/dashboard-service/dashboard/rtu/port-sensors?searchRtuName=PLN_CONS");
        const data = response.data;
        console.log(data);
    } catch(err) {
        console.error(err);
    }
};