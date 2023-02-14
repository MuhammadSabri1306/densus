import { useViewStore } from "@stores/view";
import config from "@/configs/fall-route";

export const handlingFetchErr = (err, msg = {}) => {
    console.error(err);
    const title = msg.title || config.defaultTitle;
    const message = msg.message || config.defaultMessage;
    if(config.route[err.response.status]) {
        const viewStore = useViewStore();
        viewStore.showToast(title, message, false);

        window.location.href = import.meta.env.BASE_URL + config.route[err.response.status].slice(1);
    }
};