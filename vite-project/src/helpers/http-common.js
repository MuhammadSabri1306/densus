import axios from "axios";
import { apiEndpoint, apiHeaders } from "@/configs/base";

export default axios.create({
	baseURL: apiEndpoint,
	headers: apiHeaders
});