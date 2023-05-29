<script setup>
import { ref, computed, nextTick } from "vue";
import { usePueTargetStore } from "@/stores/pue-target";
import { useUserStore } from "@stores/user";
import { useCollapseRow } from "@helpers/collapse-row";
import { getPercentageTextClass } from "@helpers/percentage-color";
import { toFixedNumber } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

defineEmits(["showCategory"]);

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const pueTargetStore = usePueTargetStore();

const tableData = ref([]);

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = pueTargetStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const getGroupAvg = (data, groupKey) => {
    const currItem = JSON.parse(JSON.stringify(data[0]));
    let rowCount = 0;
    let onlineCount = 0;
    let offlineCount = 0;

    data.forEach(item => {
        if(item[groupKey] != currItem[groupKey])
            return;
        
        if(item.is_online)
            onlineCount++;
        if(item.is_offline)
            offlineCount++;
        rowCount++;
    });

    currItem.online_count = onlineCount;
    currItem.offline_count = offlineCount;
    currItem.online_percent = 0;
    if(rowCount > 0)
        currItem.online_percent = onlineCount / rowCount * 100;
    
    return currItem;
};

const setupData = data => {
    const groupKeys = { divre: "", witel: "" };
    const groupedData = data.reduce((result, item, index) => {

        let type = null;
        let title = null;

        if(groupKeys.divre !== item.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.divre_name;
            const divreData = getGroupAvg(data.slice(index), "divre_kode");
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.divre_kode;
        }
        
        if(groupKeys.witel !== item.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.witel_name;
            const witelData = getGroupAvg(data.slice(index), "witel_kode");

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.witel_kode;

            toggleRowCollapse('witel', item.witel_kode);
        }
        
        type = "sto";
        title = item.sto_name;
        result.push({ type, title, ...item });
        return result;
        
    }, []);
    
    return groupedData;
};

const isLoading = ref(true);
const hasInit = ref(false);
const fetch = () => {
    isLoading.value = true;
    hasInit.value = true;
    pueTargetStore.getLocationStatus(({ data }) => {
        if(data.pue_status)
            tableData.value = setupData(data.pue_status);
        nextTick(() => isLoading.value = false);
    });
};

defineExpose({ fetch });

const getRowClass = item => {
    let collapsed = false;
    if(item.type == "sto") {
        const isWitelCollapsed = collapsedWitel.value.indexOf(item.witel_kode) >= 0;
        const isDivreCollapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
        collapsed = isWitelCollapsed || isDivreCollapsed;
    } else if(item.type == "witel") {
        collapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
    }
    
    return { "row-collapsed": collapsed };
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
            <table class="table table-bordered table-head-primary table-collapsable">
                <thead>
                    <tr>
                        <th class="bg-success sticky-column" rowspan="2">Lingkup Kerja</th>
                        <th class="bg-success" colspan="2">PUE</th>
                        <th class="bg-success" rowspan="2">IKE</th>
                        <th class="bg-success" rowspan="2">% PUE ONLINE</th>
                    </tr>
                    <tr>
                        <th>ONLINE</th>
                        <th>OFFLINE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in tableData" :class="getRowClass(item)">
                        <td class="sticky-column">
                            <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0, 'ms-4': level == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                            </div>
                            <p v-else class="d-block px-4 py-1 tw-whitespace-nowrap mb-0">
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                    class="fw-semibold">{{ item.title }}</small>
                            </p>
                        </td>

                        <td v-if="item.type != 'sto'" class="text-center f-w-700">{{ item.online_count }}</td>
                        <td v-else-if="item.is_online" class="middle text-center">ONLINE</td>
                        <td v-else="item.is_online" class="middle text-center f-w-700">-</td>

                        <td v-if="item.type != 'sto'" class="text-center f-w-700">{{ item.offline_count }}</td>
                        <td v-else-if="item.is_offline" class="middle text-center">OFFLINE</td>
                        <td v-else="item.is_offline" class="middle text-center f-w-700">-</td>

                        <td class="middle text-center f-w-700">-</td>

                        <td v-if="item.type != 'sto'" :class="getPercentageTextClass(item.online_percent)"
                            class="text-center f-w-700">
                            {{ toFixedNumber(item.online_percent) }}%
                        </td>
                        <td v-else></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<style scoped>

.table th {
    text-align: center;
    white-space: nowrap;
}

</style>