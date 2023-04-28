<script setup>
import { ref, computed } from "vue";
import { usePueV2Store } from "@/stores/pue-v2";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { groupByLocation } from "@helpers/location-group";
import { toFixedNumber, toRoman } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueStore = usePueV2Store();

const isAdmin = computed(() => userStore.role == "admin");
const level = computed(() => {
    const userLevel = userStore.level;
    const filters = activityStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, index, groupKey) => {
    let count = 0;
    const sum = [];
    const currItem = JSON.parse(JSON.stringify(data[index]));

    data.forEach(dataItem => {
        if(dataItem.location[groupKey] != currItem.location[groupKey])
            return;

        for(let i=0; i<currItem.item.length; i++) {
            if(sum.length <= i)
                sum[i] = 0;
            sum[i] += Number(dataItem.item[i].pue_value);
        }

        count++;
    });

    for(let i=0; i<currItem.item.length; i++) {
        currItem.item[i].pue_value = count < 1 ? 0 : sum[i] / count;
    }

    return currItem;
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };

    return data.reduce((result, item, index) => {
        let type = null;
        let title = null;

        if(groupKeys.divre !== item.location.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.location.divre_name;
            const divreData = getGroupAvg(data, index, "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.location.divre_kode;
        }
        
        if(groupKeys.witel !== item.location.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.location.witel_name;
            const witelData = getGroupAvg(data, index, "witel_kode");
            
            result.push({ type, title, ...witelData });
            groupKeys.witel = item.location.witel_kode;
        }
        
        type = "sto";
        title = item.location.lokasi;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const monthGroup = ref([]);
const colMonth = computed([() => {
    const fetchedMonth = monthGroup.value;
    const monthList = pueStore.monthList;
    const selectedMonth = pueStore.filters.month;

    if(fetchedMonth.length < 1)
        return fetchedMonth;
    if(!selectedMonth)
        return monthList;
    return [ monthList[selectedMonth - 1] ];
}]);

const dataTable = ref([]);
const setupData = rawDataLocation => {
    const monthList = pueStore.monthList;
    const month = [];
    const data = [];
    rawDataLocation.forEach((item, rowIndex) => {

        data.push({ location: item.location, pue: [] });
        item.pue.forEach((pueItem, colIndex) => {

            pueItem.pue_value = toFixedNumber(pueItem.pue_value, 2);
            data[rowIndex].pue = pueItem;

            if(month.length < colIndex - 1)
                month.push(monthList[Number(pueItem.month) - 1]);
            if(!month[colIndex].weekList)
                month[colIndex].weekList = [];
            
            const weekItem = toRoman(pueItem.week);
            if(month[colIndex].weekList.indexOf(weekItem) < 0)
                month[colIndex].weekList.push(weekItem);

        });

    });

    monthGroup.value = month;
    dataTable.value = groupData(data);
};

const isLoading = ref(true);
const fetch = () => {
    isLoading.value = true;
    pueStore.getOfflineLocationData(({ data }) => {
        if(data.location_data)
            setupData(data.location_data);
        isLoading.value = false;
    });
};

defineExpose({ fetch });
</script>
<template>

</template>