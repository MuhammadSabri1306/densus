<script setup>
import { ref, computed } from "vue";
import { usePueTargetStore } from "@/stores/pue-target";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import Skeleton from "primevue/skeleton";

const emit = defineEmits(["create", "edit"]);

const { collapsedDivre, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueTargetStore = usePueTargetStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueTargetStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const tableData = ref([]);

const getGroupAvg = (data, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[0]));
    const sum = {};

    data.forEach(item => {
        if(item.witel[groupKey] != currItem.witel[groupKey])
            return;
        
        for(let key in item.target) {
            if(!sum[key])
                sum[key] = 0;
            if(item.target[key] && item.target[key].value)
                sum[key] += Number(item.target[key].value);
        }
    });

    for(let key in currItem.target) {
        currItem.target[key] = { value: sum[key] };
    }
    
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
        result.push({ type, title, ...item });
        return result;
        
    }, []);
};

const hasInit = ref(false);
const isLoading = ref(false);
const fetch = () => {
    hasInit.value = true;
    isLoading.value = true;
    pueTargetStore.getTarget(({ data }) => {
        if(data.pue_target)
            tableData.value = groupData(data.pue_target);
        isLoading.value = false;
    })
};

defineExpose({ fetch });

const getRowClass = item => {
    let collapsed = false;
    if(item.type != "witel")
        return;
    const isDivreCollapsed = collapsedDivre.value.indexOf(item.witel.divre_kode) >= 0;
    return { "row-collapsed": isDivreCollapsed }
};

const onQuarterClick = targetItem => {
    if(targetItem)
        emit("edit", targetItem);
    else
        emit("create");
};
</script>
<template>
    <div v-if="hasInit">
        <div v-if="isLoading" class="card card-body">
            <div class="row gy-4">
                <div v-for="item in 24" class="col-3 col-lg-4">
                    <Skeleton />
                </div>
            </div>
        </div>
        <div v-else-if="tableData.length < 1" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-collapsable table-gepee-report">
                <thead>
                    <tr>
                        <th class="bg-success sticky-column">Lingkup Kerja</th>
                        <th class="bg-success">Q1</th>
                        <th class="bg-success">Q2</th>
                        <th class="bg-success">Q3</th>
                        <th class="bg-success">Q4</th>
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
                        <td v-for="target in item.target" class="position-relative f-w-700 middle text-center">
                            <a v-if="item.type != 'divre'" role="button" @click="onQuarterClick(target)" class="stretched-link">{{ target ? target.value : "-" }}</a>
                            <span v-else>{{ target ? target.value : "-" }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>