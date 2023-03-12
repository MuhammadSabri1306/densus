import { fileURLToPath, URL } from "url";
import dns from "dns";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { devBaseUrl, prodBaseUrl, resolveAlias, cors } from "./src/configs/base";

dns.setDefaultResultOrder("verbatim");

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
	const plugins = [vue()];
	const base = (command == "serve") ? devBaseUrl : prodBaseUrl;
	const server = { cors };

	const resolve = {
		alias: resolveAlias.reduce((value, currItem) => {
			value[currItem.key] = fileURLToPath(new URL(currItem.url, import.meta.url));
			return value;
		}, {})
	};

	return { plugins, base, resolve, server };
});