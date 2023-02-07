import axios from "axios";

export default axios.create({
	baseURL: "https://juarayya.telkom.co.id/densus/api",
	headers: { "Accept": "application/json" }
});