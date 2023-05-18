<script setup>
import { ref, computed, nextTick } from "vue";
import { usePueTargetStore } from "@/stores/pue-target";
import { useUserStore } from "@stores/user";
import Table1 from "./Table1.vue";
import Table2 from "./Table2.vue";

const userStore = useUserStore();
const pueTargetStore = usePueTargetStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueTargetStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const categoryList = ref([]);

const getGroupAvg = (data, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[0]));
    const sum = {
        categoryCount: [],
        stoCount: 0,
        target: 0
    };

    data.forEach(item => {
        if(item.witel[groupKey] != currItem.witel[groupKey])
            return;
        
        for(let i=0; i<categoryList.value.length; i++) {
            if(sum.categoryCount.length <= i)
                sum.categoryCount.push(0);

            const categoryKey = categoryList.value[i].category;
            sum.categoryCount[i] += item.categoryCount[categoryKey];
        }

        if(item.stoCount)
            sum.stoCount += item.stoCount;
        if(item.target)
            sum.target += item.target;
    });
    
    currItem.categoryCount = sum.categoryCount;
    currItem.stoCount = sum.stoCount;
    currItem.target = sum.target;
    currItem.gap = 0;
    currItem.percentage = 0;
    if(sum.categoryCount > 0) {
        currItem.gap = sum.target - sum.categoryCount[0];
        if(sum.target)
            currItem.percentage = sum.categoryCount[0] / sum.target * 100;
    }

    delete currItem.witel.witel_kode;
    delete currItem.witel.witel_name;
    
    return currItem;
};

const groupData = data => {
    const groupKeys = { divre: "" };
    return data.reduce((result, item, index) => {

        let type = null;
        let title = null;

        if(groupKeys.divre !== item.witel.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.witel.divre_name;
            const divreData = getGroupAvg(data.slice(index), "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.witel.divre_kode;
        }
        
        type = "witel";
        title = item.witel.witel_name;

        const categoryCount = categoryList.value.map(catItem => item.categoryCount[catItem.category]);
        item.categoryCount = categoryCount;
        
        item.gap = 0;
        item.percentage = 0;
        if(categoryCount.length > 0){
            item.gap = categoryCount[0] - item.target;
            if(item.target)
                item.percentage = categoryCount[0] / item.target * 100;
        }
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const table1 = ref(null);
const table2 = ref(null);

const showTable = ref(false);

const updateTableData = () => {
    pueTargetStore.getReport(({ data }) => {
        const tableValues = {};
        if(data.pue_category) {
            categoryList.value = data.pue_category;
            tableValues.categoryList = data.pue_category;
        }

        if(data.pue_target) {
            tableValues.tableData = groupData(data.pue_target);
        }

        table1.value.setData(tableValues);
        table2.value.setData(tableValues);
        if(!data.pue_target || data.pue_target.length < 1)
            showTable.value = false;
    });
};

const fetch = () => {
    showTable.value = true;
    table1.value.setLoading(true);
    table1.value.setHasInit(true);
    table2.value.setLoading(true);
    table2.value.setHasInit(true);
    nextTick(updateTableData);
};

defineExpose({ fetch });
</script>
<template>
    <div>
        <div v-if="!showTable" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <Table1 ref="table1" v-show="showTable" class="mb-5" />
        <Table2 ref="table2" v-show="showTable" />
    </div>
</template>