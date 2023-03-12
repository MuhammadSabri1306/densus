<script setup>
import { ref, computed, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useActivityStore } from "@stores/activity";
import Skeleton from "primevue/skeleton";
import DialogActivityCategory from "@components/DialogActivityCategory.vue";
// import sampleSchedule from "@/helpers/sample-data/schedule";
// import DataTable from "primevue/datatable";
// import Column from "primevue/column";

const monthList = ["Januari", "Februari", "Maret",
    "April", "Mei", "Juni", "Juli", "Agustus",
    "September", "Oktober", "November", "Desember"];

const isLoaded = ref(false);
const isLoading = ref(false);

const activityStore = useActivityStore();
const collapsedDivre = ref([]);
const collapsedWitel = ref([]);

const collapseRow = (type, code) => {
    if(type == "divre")
        collapsedDivre.value = [...collapsedDivre.value, code];
    if(type == "witel")
        collapsedWitel.value = [...collapsedWitel.value, code];
};

const expandRow = (type, code) => {
    if(type == "divre")
        collapsedDivre.value = collapsedDivre.value.filter(item => item !== code);
    if(type == "witel")
        collapsedWitel.value = collapsedWitel.value.filter(item => item !== code);
};

const toggleRowCollapse = (type, code) => {
    const isExpand = (type == "divre") ? collapsedDivre.value.indexOf(code) < 0 : collapsedWitel.value.indexOf(code) < 0;
    if(isExpand)
        collapseRow(type, code);
    else
        expandRow(type, code);
};

const locationList = computed(() => {
    const list = activityStore.location;

    const result = [];
    let divreCode = "";
    let witelCode = "";
    list.forEach((item, index) => {
        
        let type = null;
        let title = null;
        
        if(divreCode !== item.divre_kode) {
            type = "divre";
            title = item.divre_name;
            result.push({ type, title, ...item });
            divreCode = item.divre_kode;
        }
        
        if(witelCode !== item.witel_kode) {
            type = "witel";
            title = item.witel_name;
            result.push({ type, title, ...item });
            witelCode = item.witel_kode;
        }
        
        type = "sto";
        title = item.nama_pel_pln;
        result.push({ type, title, ...item });
        
    });

    return result;
});

const fetch = async () => {
    await activityStore.fetchAvailableMonth();

    isLoading.value = true;
    await activityStore.fetchLocation(true, response => {
        isLoading.value = false;
        isLoaded.value = response.success;
    });

    await activityStore.fetchCategory();

    await activityStore.fetchSchedule();
};

defineExpose({ fetch });

const showCategory = ref(false);

const fieldItem = computed(() => {
    const category = activityStore.category;
    const list = monthList.reduce((res, month) => {
        if(category.length < 1)
            return [];

        if(!Array.isArray(res)) {
            const firstMonth = res;
            res = [{ month: firstMonth, category: { number: 1, ...category[0] } }];

            for(let i=1; i<category.length; i++) {
                res.push({ month: firstMonth, category: { number: i+1, ...category[i] } });
            }
        }
        
        for(let j=0; j<category.length; j++) {
            res.push({ month, category: { number: j+1, ...category[j] } });
        }
        
        return res;
    });

    console.log(list)
    return list;
})

const { isMonthAvailable } = storeToRefs(activityStore);
</script>
<template>
    <div v-if="isLoaded" :class="{ 'card': isLoading }">
        <div v-if="!isLoading">
            <div class="py-4 d-flex justify-content-end">
                <button type="button" @click="showCategory = true" class="btn btn-outline-info bg-white btn-icon px-3">
                    <VueFeather type="info" size="1em" />
                    <span class="ms-2">Keterangan</span>
                </button>
            </div>
            <div class="table-responsive bg-white pb-3">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2">LINGKUP KERJA</th>
                            <th v-for="item in monthList" colspan="8">{{ item }}</th>
                        </tr>
                        <tr>
                            <th v-for="item in fieldItem">
                                <p class="text-center mb-0">{{ item.category?.number }}</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in locationList" :class="{ 'row-collapsed': (item.type == 'sto' && collapsedWitel.indexOf(item.witel_kode) >= 0) || (item.type == 'witel' && collapsedDivre.indexOf(item.divre_kode) >= 0) }">
                            <td>
                                <div v-if="item.type == 'divre'" class="d-flex align-items-center">
                                    <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                        <VueFeather type="chevron-right" size="1rem" />
                                    </button>
                                    <span class="ms-2 tw-whitespace-nowrap">{{ item.title }}</span>
                                </div>
                                <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                    <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                        <VueFeather type="chevron-right" size="1rem" />
                                    </button>
                                    <span class="ms-2 tw-whitespace-nowrap">{{ item.title }}</span>
                                </div>
                                <div v-else class="px-4 py-1 tw-whitespace-nowrap"><span class="ps-5">{{ item.title }}</span></div>
                            </td>
                            <template v-if="item.type == 'sto'">
                                <td v-for="field in fieldItem">
                                    <input type="checkbox" :title="field.category?.activity + ' bulan ' + field.month" class="form-check-input" />
                                </td>
                            </template>
                            <template v-else>
                                <td colspan="96" class="bg-light"></td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else class="card-body">
            <div class="m-t-30">
                <div v-for="x in 4" class="row mb-5">
                    <div v-for="x in 4" class="col"><Skeleton height="2rem" /></div>
                </div>
            </div>
        </div>
        <DialogActivityCategory v-model:visible="showCategory" />
    </div>
</template>
<style scoped>

tr.row-collapsed {
    display: none;
}

.btn-collapse-row {
    @apply tw-rotate-90 tw-transition-transform tw-duration-300;
}

.btn-collapse-row.child-collapsed {
    @apply tw-rotate-0;
}

.table thead th {
    background-color: #24695c!important;
    color: #fff!important;
    text-align: center;
}

</style>