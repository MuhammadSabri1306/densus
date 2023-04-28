<script setup>
import { ref, computed } from "vue";
import { usePlnStore } from "@stores/pln";
import { useUserStore } from "@stores/user";
import { groupByLocation } from "@helpers/location-group";
import { useCollapseRow } from "@helpers/collapse-row";
import { toFixedNumber } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";
import ButtonGroupAction from "@/components/ButtonGroupAction.vue";

defineEmits(["create", "edit", "detail"]);
const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const userStore = useUserStore();
const userRole = computed(() => userStore.role);

const plnStore = usePlnStore();
const filters = ref(plnStore.filters);
const level = computed(() => {
    const userLevel = userStore.level;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const data = ref([]);
const tableData = computed(() => {
    const dataValue = data.value;
    const list = groupByLocation({
        data: dataValue,
        divre: {
            groupKey: "divre_kode",
            checker: isMatch => isMatch && level.value == "nasional",
            formatTitle: item => item.divre_name
        },
        witel: {
            groupKey: "witel_kode",
            checker: isMatch => isMatch && level.value != "witel",
            formatTitle: item => "WILAYAH " + item.witel_name
        },
        sto: { formatTitle: item => item.lokasi }
    });
    
    return list;
});

const isLoading = ref(true);
const fetch = () => {
    isLoading.value = true;
    plnStore.getParams(response => {
        data.value = response.data;
        filters.value = plnStore.filters;
        isLoading.value = false;
    });
};

defineExpose({ fetch });
</script>
<template>
    <div>
        <div v-if="!isLoading">
            <div v-if="tableData.length < 1" class="px-4 py-3 border">
                <h4 class="text-center">Belum ada data.</h4>
                <div v-if="userRole == 'admin'" class="d-flex justify-content-center">
                    <button type="button" @click="$emit('create')" class="btn btn-icon btn-outline-info">
                        <VueFeather type="plus" size="1.2em" />
                        <span class="ms-1">Buat Parameter Baru</span>
                    </button>
                </div>
            </div>
            <div v-else-if="userRole == 'admin'" class="d-flex justify-content-end mb-4">
                <button type="button" @click="$emit('create')" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">Buat Parameter Baru</span>
                </button>
            </div>
        </div>
        <div class="table-responsive bg-white">
            <table class="table table-bordered table-head-primary table-pue">
                <thead>
                    <tr>
                        <th rowspan="2" class="bg-success">Lingkup Kerja</th>
                        <th colspan="7">Parameter</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>Port KWH 1 (Low)</th><th>Port KWH 1 (High)</th>
                        <th>Port KWH 2 (Low)</th><th>Port KWH 2 (High)</th>
                        <th>Port KVA</th><th>Port KW</th>
                        <th>Port Power Factor</th>
                    </tr>
                </thead>
                <tbody v-if="isLoading">
                    <tr v-for="x in 5">
                        <td><Skeleton /></td>
                        <td colspan="8"><Skeleton /></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="item in tableData" :class="{ 'row-collapsed': ((item.type == 'sto' && collapsedWitel.indexOf(item.witel_kode) >= 0) || (item.type == 'sto' && collapsedDivre.indexOf(item.divre_kode) >= 0)) || (item.type == 'witel' && collapsedDivre.indexOf(item.divre_kode) >= 0) }">
                        <td :colspan="item.type == 'sto' ? 1 : 9">
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
                                <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                            </p>
                        </td>
                        <template v-if="item.type == 'sto'">
                            <td class="text-center middle">{{ item.port_kwh1_high }}</td>
                            <td class="text-center middle">{{ item.port_kwh1_high }}</td>
                            <td class="text-center middle">{{ item.port_kwh2_low }}</td>
                            <td class="text-center middle">{{ item.port_kwh2_high }}</td>
                            <td class="text-center middle">{{ item.port_kva }}</td>
                            <td class="text-center middle">{{ item.port_kw }}</td>
                            <td class="text-center middle">{{ item.port_power_factor }}</td>
                            <td class="text-center middle">
                                <ButtonGroupAction @detail="$emit('detail', item)" @edit="$emit('edit', item)" :useBtnDelete="false" />
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>