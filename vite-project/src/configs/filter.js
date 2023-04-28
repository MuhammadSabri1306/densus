
export const getYearConfig = () => {
    const startYear = 2023;
    const endYear = new Date().getFullYear();

    const startList = startYear - 4;
    const endList = endYear + 4;
    
    return { startYear, endYear, startList, endList };
};

export const getYearListConfig = () => {
    const { startYear, endYear, startList, endList } = getYearConfig();
    const list = [];
    
    for(let year=startList; year<=endList; year++) {
        const isDisabled = year < startYear || year > endYear;
        list.push({ year, isDisabled });
    }

    return list;
};