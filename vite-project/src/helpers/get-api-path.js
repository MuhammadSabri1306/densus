
export const getApiPath = (url, getParams) => {
    const params = [];
    for(let key in getParams) {
        if(getParams[key])
            params.push(`${ key }=${ getParams[key] }`);
    }
    
    if(params.length < 0)
        return url;
    
    url += "?" + params.join("&");
    return url;
};