
export const allowSampleData = import.meta.env.VITE_ALLOW_SAMPLE_DATA == 1;
export const baseUrl = import.meta.env.VITE_BASE_URL;
export const backendUrl = import.meta.env.VITE_BACKEND_URL;

export const apiEndpoint = import.meta.env.VITE_APP_ENDPOINT;
export const apiHeaders = {
    "Access-Control-Allow-Origin": "*",
    "Content-Type": "application/json",
    "Accept": "application/json"
};