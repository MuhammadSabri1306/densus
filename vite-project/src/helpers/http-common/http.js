import axios from "axios";
import { apiEndpoint, apiHeaders } from "@/configs/base";

export const createHttpInstance = (baseURL, config = {}) => {
    return axios.create({
        baseURL,
        ...config
    });
};

export const http = createHttpInstance(apiEndpoint, { headers: apiHeaders });