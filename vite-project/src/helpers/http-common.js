import axios from "axios";

export default axios.create({
	baseURL: "https://juarayya.telkom.co.id/densus/api",
	headers: {
		// 'Access-Control-Allow-Origin': '*',
		'Content-Type': 'application/json',
		"Accept": "application/json"
	}
});