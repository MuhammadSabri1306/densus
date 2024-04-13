import { fileURLToPath, URL } from "url";
import dns from "dns";
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { cors } from "./src/configs/server";

dns.setDefaultResultOrder("verbatim");

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {

	const env = loadEnv(mode, process.cwd());
	return {
		plugins: [vue()],
		base: env.VITE_BASE_URL,
		resolve: {
			alias: {
				"@": fileURLToPath(new URL("./src", import.meta.url))
			}
		},
		server: { cors }
	};

});