import { reactive } from "vue";

export const getDefaultConfig = () => {
    const config = {
        interval: 300,
        initVal: false,
        changesVal: true
    };
    return JSON.parse(JSON.stringify(config));
};

const readConfig = config => {
    const defaultConfig = getDefaultConfig();
    for(let key in defaultConfig) {
        if(!config[key])
            config[key] = defaultConfig[key];
    }
    return config;
};

export const useSequenceState = (keys = [], config) => {
    const { interval, initVal, changesVal } = readConfig(config);
    const initData = {};
    keys.forEach(item => initData[item] = initVal);

    const state = reactive(initData);
    let changedIndex = 0;
    const timeInterval = setInterval(() => {
        
    }, interval);
};