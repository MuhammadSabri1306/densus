<script setup>
import { ref, computed } from "vue";
import { useOxispCheckStore } from "@/stores/oxisp-check";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import { useCollapseRow } from "@helpers/collapse-row";
import Skeleton from "primevue/skeleton";

const emit = defineEmits(["showCategory", "selectCheck"]);

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();
const userStore = useUserStore();
const oxispCheckStore = useOxispCheckStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = oxispCheckStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const tableData = ref([]);
const roomList = ref([]);
const categoryList = computed(() => oxispCheckStore.categories);
const columnCount = computed(() => roomList.value.length * oxispCheckStore.categories.length);

const viewStore = useViewStore();
const selectedYear = computed(() => viewStore.filters.year);
const selectedMonth = computed(() => {
    const monthNumber = viewStore.filters.month || (new Date()).getMonth() + 1;
    return viewStore.monthList[monthNumber - 1];
});

const hasCollapseInit = ref(false);
const resetCollapse = () => {
    collapsedDivre.value = [];
    collapsedWitel.value = [];
    hasCollapseInit.value = false;
};

const buildCollapseData = (level, item) => {
    if(level == "divre") {
        const { divre_kode, divre_name } = item;
        return { divre_kode, divre_name };
    }

    if(level == "witel") {
        const { divre_kode, divre_name, witel_kode, witel_name } = item;
        return { divre_kode, divre_name, witel_kode, witel_name };
    }

    return item;
};

const groupData = data => {
    const groupKeys = { divre: "", witel: "" };
    const result = data.reduce((result, item, index) => {

        let type = null;
        let title = null;

        if(groupKeys.divre !== item.divre_kode && level.value == "nasional") {
            type = "divre";
            title = item.divre_name;
            const divreData = buildCollapseData("divre", item);
            
            result.push({ type, title, ...divreData });
            groupKeys.divre = item.divre_kode;

            const hasDivreCollapsed = collapsedDivre.value.indexOf(item.divre_kode) >= 0;
            if(!hasCollapseInit.value && !hasDivreCollapsed)
                toggleRowCollapse("divre", item.divre_kode);
        }
        
        if(groupKeys.witel !== item.witel_kode && level.value != "witel") {
            type = "witel";
            title = "WILAYAH " + item.witel_name;
            const witelData = buildCollapseData("witel", item);

            result.push({ type, title, ...witelData });
            groupKeys.witel = item.witel_kode;

            const hasWitelCollapsed = collapsedWitel.value.indexOf(item.witel_kode) >= 0;
            if(!hasCollapseInit.value && !hasWitelCollapsed)
                toggleRowCollapse("witel", item.witel_kode);
        }
        
        type = "sto";
        title = item.sto_name;

        result.push({ type, title, ...item });
        return result;
        
    }, []);

    if(!hasCollapseInit.value)
        hasCollapseInit.value = true;
    return result;
};

const hasInit = ref(false);
const isLoading = ref(false);

const fetch = () => {
    isLoading.value = true;
    oxispCheckStore.getList(({ success, data }) => {
        if(data.check_data) {
            resetCollapse();
            tableData.value = groupData(data.check_data);
            hasInit.value = true;
        } else
            hasInit.value = success;
        if(data.rooms)
            roomList.value = data.rooms;

        isLoading.value = false;
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

const isAdmin = computed(() => userStore.role == "admin");
const isCheckClickable = item => {
    if(!item.is_enable)
        return false;
    if(isAdmin.value && !item.is_exists)
        return false;
    return true;
};

const getTdClass = item => {
    if(!isCheckClickable(item))
        return "bg-cell-mute";
    if(!item.is_exists)
        return;
    if(item.check_value.status == "rejected")
        return "bg-cell-danger";
    if(item.check_value.status == "submitted")
        return "bg-cell-warning";
    return "bg-cell-success";
};

const getCheckLabel = item => {
    return `Checklist ${ item?.category.code } ${ item?.room.name }`;
};

const onCheckClick = (rowData, check) => {
    const { check_list, ...loc } = rowData;
    emit("selectCheck", { loc, check });
};
</script>
<template>
    <div>
        <div v-if="isLoading" class="card card-body">
            <div class="row gy-4">
                <div v-for="item in 24" class="col-3 col-lg-4">
                    <Skeleton />
                </div>
            </div>
        </div>
        <div v-else-if="!hasInit" class="px-4 py-3 border">
            <h4 class="text-center">Gagal memuat data.</h4>
        </div>
        <div v-else-if="tableData.length < 1" class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
        </div>
        <div v-else class="table-responsive table-freeze bg-white pb-3">
            <table class="table table-bordered table-head-primary table-collapsable table-gepee-report">
                <thead>
                    <tr>
                        <th rowspan="3" class="bg-success sticky-column">Lingkup Kerja</th>
                        <th :colspan="columnCount" class="bg-success">
                            OX ISP Checklist <small>Bulan {{ selectedMonth.name }} Tahun {{ selectedYear }}</small>
                        </th>
                    </tr>
                    <tr>
                        <th v-for="room in roomList" :colspan="categoryList.length" class="bg-success">{{ room.name }}</th>
                    </tr>
                    <tr>
                        <template v-for="n in roomList">
                            <th v-for="category in categoryList" @click="$emit('showCategory')"
                                class="tw-cursor-pointer btn-primary category-tooltip tw-uppercase">
                                <p class="text-center mb-0">{{ category.code }}</p>
                                <span class="badge badge-light text-dark border-primary">{{ category.title }}</span>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in tableData" :class="getRowClass(row)">
                        <td class="sticky-column">
                            <div v-if="row.type == 'divre'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('divre', row.divre_kode)"
                                    :class="{ 'child-collapsed': collapsedDivre.indexOf(row.divre_kode) >= 0 }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
                            <div v-else-if="row.type == 'witel'" class="d-flex align-items-center px-3">
                                <button type="button" @click="toggleRowCollapse('witel', row.witel_kode)"
                                    :class="{ 'child-collapsed': collapsedWitel.indexOf(row.witel_kode) >= 0, 'ms-4': level == 'nasional' }"
                                    class="btn btn-circle btn-light p-0 btn-collapse-row">
                                    <VueFeather type="chevron-right" size="1rem" />
                                </button>
                                <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ row.title }}</small>
                            </div>
                            <small v-else :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }"
                                class="fw-semibold">{{ row.title }}</small>
                        </td>
                        <template v-if="row.type == 'sto'">
                            <td v-for="item in row.check_list" :class="getTdClass(item)" class="text-center middle">
                                <input type="checkbox" :checked="item.is_exists" @click.prevent="onCheckClick(row, item)"
                                    :disabled="!isCheckClickable(item)" :aria-label="getCheckLabel(item)"
                                    class="form-check-input form-check-input-lg" />
                            </td>
                        </template>
                        <td v-else :colspan="columnCount" class="bg-cell-mute"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<style scoped>

tbody td:first-child small {
    @apply tw-whitespace-nowrap;
}

td.bg-cell-success {
    background-color: #2cb198!important;
}

td.bg-cell-warning {
    background-color: #ffe55c!important;
}

td.bg-cell-danger {
    background-color: #ff658d!important;
}

td.bg-cell-mute {
    @apply !tw-bg-zinc-100/50;
}

.category-tooltip {
    @apply tw-relative;
}

.category-tooltip .badge {
    @apply tw-absolute tw-right-0 -tw-top-8 tw-z-[9] tw-transition-opacity tw-opacity-0 tw-pointer-events-none;
}

.category-tooltip:hover .badge {
    @apply tw-opacity-100;
}

</style>