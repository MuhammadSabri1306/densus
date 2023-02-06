import { createApp } from "vue";
import App from "./App.vue";

import router from "./configs/router";
import { createPinia } from "pinia";

import VueFeather from "vue-feather";
import "bootstrap/dist/css/bootstrap.min.css";
// import "bootstrap";
import "./assets/css/app.css";

const pinia = createPinia();

createApp(App)
    .use(pinia)
    .use(router)
    .component(VueFeather.name, VueFeather)
    .mount("#app");