import { fileURLToPath, URL } from "url";
import dns from "dns";
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { resolveAlias, cors } from "./src/configs/server";

dns.setDefaultResultOrder("verbatim");

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, process.cwd());

	const plugins = [vue()];
	const base = env.VITE_BASE_URL;
	const server = { cors };

	const resolve = {
		alias: resolveAlias.reduce((value, currItem) => {
			value[currItem.key] = fileURLToPath(new URL(currItem.url, import.meta.url));
			return value;
		}, {})
	};

	return { plugins, base, resolve, server };
});