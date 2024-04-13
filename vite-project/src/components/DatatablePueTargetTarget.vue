<script setup>
import { ref, computed, watch } from "vue";
import { usePueTargetStore } from "@/stores/pue-target";
import { useUserStore } from "@/stores/user";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toNumberText } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const { collapsedDivre, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueTargetStore = usePueTargetStore();

const tableData = ref(null);
const categoryList = computed(() => pueTargetStore.category);
const isLoading = computed(() => pueTargetStore.isLoading.report);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueTargetStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

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
    currItem.gap = sum.target - sum.categoryCount[0];
    currItem.percentage = sum.target < 1 ? 100 : sum.categoryCount[0] / sum.target * 100;

    // if(sum.target > 0) {
    //     currItem.gap = sum.target - sum.categoryCount[0];
        // if(sum.target)
        //     currItem.percentage = sum.categoryCount[0] / sum.target * 100;
    // }

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
        item.gap = item.target - item.categoryCount[0];
        item.percentage = item.target < 1 ? 100 : item.categoryCount[0] / item.target * 100;
        
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const watcherSrc = () => {
    const target = pueTargetStore.target;
    const category = pueTargetStore.category;
    return { target, category };
};

const watcherCall = ({ target, category }) => {
    if(target && category)
        tableData.value = groupData(JSON.parse(JSON.stringify(target)));
};

watch(watcherSrc, watcherCall);
watcherCall(watcherSrc());

const getRowClass = item => {
    if(item.type != "witel")
        return;
    const isDivreCollapsed = collapsedDivre.value.indexOf(item.witel.divre_kode) >= 0;
    return { "row-collapsed": isDivreCollapsed }
};
</script>
<template>
    <div v-if="tableData || isLoading">
        <div v-if="isLoading" class="card">
            <div class="card-header bg-primary">
                <h5>Target PUE</h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">
                    <div v-for="item in 24" class="col-3 col-lg-4">
                        <Skeleton />
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="table-responsive table-freeze bg-white">
            <table class="table table-bordered table-head-primary table-collapsable">
                <thead>
                    <tr>
                        <th colspan="5" class="px-4">
                            <h5 class="f-w-700 text-start mb-0">Target PUE</h5>
                        </th>
                    </tr>
                    <tr>
                        <th class="bg-success sticky-column">Lingkup Kerja</th>
                        <th v-if="categoryList.length > 0" class="bg-success">{{ categoryList[0].title }}</th>
                        <th class="bg-success">Target</th>
                        <th class="bg-success">Gap</th>
                        <th class="bg-success">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in tableData" :class="getRowClass(item)">
                        <td class="sticky-column">

                            <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', item.witel.divre_kode)"
                                    :class="{ 'child-collapsed': collapsedDivre.indexOf(item.witel.divre_kode) >= 0 }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>

                            <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                <small :class="{ 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                            </p>
                            
                        </td>
                        <td v-if="categoryList.length > 0" class="middle text-center">{{ item.categoryCount[0] }}</td>
                        <td class="middle text-center">{{ item.target }}</td>
                        <td class="middle text-center">{{ item.gap }}</td>
                        <td class="middle text-center">{{ item.percentage !== null ? toNumberText(item.percentage) : null }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>