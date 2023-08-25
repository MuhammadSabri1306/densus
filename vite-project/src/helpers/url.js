export const createUrlParams = params => {
    const urlParams = new URLSearchParams();
    for(let key in params) {
        urlParams.append(key, params[key]);
    }
    return urlParams.toString();
};