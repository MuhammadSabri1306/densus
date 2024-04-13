import { useViewStore } from "@/stores/view";
import config from "@/configs/fall-route";

export const handlingFetchErr = (err, msg = {}) => {
    console.error(err);
    let title = msg.title || config.defaultTitle;
    let message = msg.message || config.defaultMessage;

    if(err.response && err.response.data) {
        message = err.response.data.message || message;
    }
    
    const viewStore = useViewStore();
    viewStore.showToast(title, message, false);
    
    if(err.response && config.route[err.response.status])
        window.location.href = import.meta.env.BASE_URL + config.route[err.response.status].slice(1);
};