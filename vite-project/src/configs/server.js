export const cors = {
    "origin": "*",
    "methods": "GET,HEAD,PUT,PATCH,POST,DELETE",
    "preflightContinue": false,
    "optionsSuccessStatus": 204,
    "allowedHeaders": ['Origin', "X-Requested-With", "Content-Type", "Accept", "Access-Control-Request-Method", "Authorization"]
};