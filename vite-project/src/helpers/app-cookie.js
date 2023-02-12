import Cookies from "js-cookie";

const cookiePrefix = "densus_app_cookie";
const getName = key => cookiePrefix + "_" + key;

export const getCookie = key => {
	const data = Cookies.get(getName(key));
	if(data === undefined)
		return null;
	return JSON.parse(data);
};

export const setCookie = (key, val, expires = null) => {
	Cookies.remove(getName(key));
	const dataJson = JSON.stringify(val);

	if(!expires)
		Cookies.set(getName(key), dataJson);
	else
		Cookies.set(getName(key), dataJson, { expires });
};

export const deleteCookie = key => {
	Cookies.remove(getName(key), { path: "" });
};