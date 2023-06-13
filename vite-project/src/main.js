import { createApp } from "vue";
import App from "./App.vue";

import router from "./configs/router";
import { createPinia } from "pinia";

import VueFeather from "vue-feather";
// import "bootstrap/dist/css/bootstrap.min.css";

import PrimeVue from "primevue/config";
import Tooltip from "primevue/tooltip";
import "primevue/resources/primevue.min.css";
import "primeicons/primeicons.css";
import "primevue/resources/themes/lara-light-teal/theme.css";

import "./assets/css/loader.css";
// import "./assets/css/app.css";

const pinia = createPinia();

createApp(App)
    .use(pinia)
    .use(router)
    .use(PrimeVue)
    .component(VueFeather.name, VueFeather)
    .directive('tooltip', Tooltip)
    .mount("#app");