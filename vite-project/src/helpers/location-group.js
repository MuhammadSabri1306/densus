export const groupConfig = {
    apply: false,
    groupKey: "",
    checker: isMatch => isMatch && true,
    formatTitle: item => ""
};

export const getBasicConfig = () => ({
    data: [],
    averagedKeys: [],
    divre: JSON.parse(JSON.stringify(groupConfig)),
    witel: JSON.parse(JSON.stringify(groupConfig)),
    sto: { formatTitle: item => "" }
});

export const getGroupAvg = (data, index, averagedKeys, groupConfig) => {
    const dataItem = data[index];
    if(averagedKeys.length < 1)
        return dataItem;
    
    let count = 0;
    const sum = {};
    averagedKeys.forEach(avgKey => sum[avgKey] = 0);

    data.forEach(item => {
        if(item[groupConfig.groupKey] != dataItem[groupConfig.groupKey])
            return;

        averagedKeys.forEach(avgKey => {
            sum[avgKey] += Number(item[avgKey]);
        });

        count++;
    });

    const result = {};
    averagedKeys.forEach(avgKey => result[avgKey] = count < 1 ? 0 : sum[avgKey] / count);
    return { ...dataItem, ...result };
};

export const listToGroup = ({ data, averagedKeys, divre, witel, sto }) => {
    const groupKeys = { divre: "", witel: "" };

    return data.reduce((result, item, index) => {
        let type = null;
        let title = null;

        if(divre.apply && divre.checker(groupKeys.divre !== item[divre.groupKey])) {
            type = "divre";
            title = divre.formatTitle(item);
            const divreData = getGroupAvg(data, index, averagedKeys, divre);
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item[divre.groupKey];
        }
        
        if(witel.apply && witel.checker(groupKeys.witel !== item[witel.groupKey])) {
            type = "witel";
            title = witel.formatTitle(item);
            const witelData = getGroupAvg(data, index, averagedKeys, witel);
            
            result.push({ type, title, ...witelData });
            groupKeys.witel = item[witel.groupKey];
        }
        
        type = "sto";
        title = sto.formatTitle(item);
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

export const groupByLocation = (params = {}) => {
    const config = getBasicConfig();

    if(params.data) config.data = params.data;
    if(params.averagedKeys) config.averagedKeys = params.averagedKeys;
    if(params.sto && params.sto.formatTitle) config.sto.formatTitle = params.sto.formatTitle;

    if(params.divre) {
        config.divre.apply = true;
        if(params.divre.groupKey) config.divre.groupKey = params.divre.groupKey;
        if(params.divre.checker) config.divre.checker = params.divre.checker;
        if(params.divre.formatTitle) config.divre.formatTitle = params.divre.formatTitle;
    }

    if(params.witel) {
        config.witel.apply = true;
        if(params.witel.groupKey) config.witel.groupKey = params.witel.groupKey;
        if(params.witel.checker) config.witel.checker = params.witel.checker;
        if(params.witel.formatTitle) config.witel.formatTitle = params.witel.formatTitle;
    }

    return listToGroup(config);
};