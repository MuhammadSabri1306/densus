import axios from "axios";

export default axios.create({
	baseURL: "http://localhost/densus/api",
	headers: { "Accept": "application/json" }
});