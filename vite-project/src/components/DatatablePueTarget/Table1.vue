<script setup>
import { ref, computed } from "vue";
import { usePueTargetStore } from "@/stores/pue-target";
import { useUserStore } from "@/stores/user";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toNumberText } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";

const { collapsedDivre, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueTargetStore = usePueTargetStore();

const tableData = ref([]);
const categoryList = ref([]);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueTargetStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const isLoading = ref(false);
const setLoading = val => isLoading.value = val;

const hasInit = ref(false);
const setHasInit = val => hasInit.value = val;

const setData = (data, isLoadingValue = false) => {
    if(data.categoryList)
        categoryList.value = data.categoryList;
    if(data.tableData)
        tableData.value = data.tableData;
    isLoading.value = isLoadingValue;
};

defineExpose({ setLoading, setHasInit, setData });

const getRowClass = item => {
    let collapsed = false;
    if(item.type != "witel")
        return;
    const isDivreCollapsed = collapsedDivre.value.indexOf(item.witel.divre_kode) >= 0;
    return { "row-collapsed": isDivreCollapsed }
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
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-collapsable">
                <thead>
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
                        <td class="middle text-center">{{ toNumberText(item.percentage) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>