import { useRouter } from "vue-router";
import { useViewStore } from "@stores/view";
import config from "@/configs/fall-route";

export const handlingFetchErr = (err, msg = {}) => {
    console.error(err);
    const title = msg.title || config.defaultTitle;
    const message = msg.message || config.defaultMessage;

    if(config.route[err.status]) {
        const router = useRouter();
        const viewStore = useViewStore();

        viewStore.showToast(title, message, false);
        router.push(fallRoutes[err.status]);
    }
};