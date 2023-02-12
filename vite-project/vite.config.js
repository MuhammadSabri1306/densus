import { fileURLToPath, URL } from "url";
import dns from "dns";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

dns.setDefaultResultOrder("verbatim");

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
	return {
		plugins: [vue()],
		base: command == "serve" ? "/" : "/",
		resolve: {
			alias: {
				"@components": fileURLToPath(new URL("./src/components", import.meta.url)),
				"@views": fileURLToPath(new URL("./src/views", import.meta.url)),
				"@stores": fileURLToPath(new URL("./src/stores", import.meta.url)),
				"@helpers": fileURLToPath(new URL("./src/helpers", import.meta.url)),
				"@layouts": fileURLToPath(new URL("./src/layouts", import.meta.url)),
				"@": fileURLToPath(new URL("./src", import.meta.url))
			}
		}
	};
});