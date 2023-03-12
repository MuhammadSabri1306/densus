
export const allowSampleData = true;

export const devBaseUrl =  "/";
export const prodBaseUrl =  "/densus/";

export const resolveAlias = [
    { key: "@components", url: "./src/components" },
    { key: "@views", url: "./src/views" },
    { key: "@stores", url: "./src/stores" },
    { key: "@helpers", url: "./src/helpers" },
    { key: "@layouts", url: "./src/layouts" },
    { key: "@", url: "./src" }
];

export const cors = {
    "origin": "*",
    "methods": "GET,HEAD,PUT,PATCH,POST,DELETE",
    "preflightContinue": false,
    "optionsSuccessStatus": 204,
    "allowedHeaders": ['Origin', "X-Requested-With", "Content-Type", "Accept", "Access-Control-Request-Method", "Authorization"]
};

export const apiEndpoint = "https://juarayya.telkom.co.id/densus/api";
export const apiHeaders = {
    "Access-Control-Allow-Origin": "*",
    "Content-Type": "application/json",
    "Accept": "application/json"
};