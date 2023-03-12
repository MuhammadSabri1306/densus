export const groupRow = (data, groupKey, headerFields) => {
    const headerItem = data.map(item => item[groupKey]);
    const groupList = [...new Set(headerItem)];
    
    const result = [];
    let groupIndex = -1;
    data.forEach(item => {
        
        if(item[groupKey] !== groupList[groupIndex]) {
            groupIndex++;
            
            const header = {
                isExpander: true,
                targetKey: groupKey
            };
            
            header[groupKey] = item[groupKey];
            headerFields.forEach(hItem => {
                header[hItem] = item[hItem];
            });
            
            result.push(header);
        }
        
        result.push(item);
    });
    
    return result;
};