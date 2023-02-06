import performance from "./performance";
import bbmCost from "./bbmCost";
import kwhReal from "./kwhReal";
import savingPercent from "./savingPercent";
import kwhTotal from "./kwhTotal";
import kwhToday from "./kwhToday";
import totalAlarm from "./totalAlarm";
import tableData from "./tableData";
import degTableData from "./degTableData";
import chartDataDaily from "./chartDataDaily";

const datas = { performance, bbmCost, kwhReal, savingPercent, kwhTotal, kwhToday, totalAlarm, tableData, degTableData, chartDataDaily };

export const useMonitoringSampleData = (key) => {
    return new Promise((resolve, reject) => {
        setTimeout(() => resolve({ data: datas[key] }), 600);
    });
};