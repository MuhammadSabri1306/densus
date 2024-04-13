<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@/stores/user";
import { useRoute } from "vue-router";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import SectionGepeeEvidenceInfo from "@/components/SectionGepeeEvidenceInfo.vue";
import FilterGepeeEvd from "@/components/FilterGepeeEvd.vue";
import SectionGepeeEvidenceCategory from "@/components/SectionGepeeEvidenceCategory.vue";
import DialogGepeeEvidence from "@/components/DialogGepeeEvidence.vue";

const divreCode = ref(null);
const witelName = ref(null);
const onInfoFetched = data => {
    divreCode.value = (!data.location || !data.location.divre_kode) ? null : data.location.divre_kode;
    witelName.value = (!data.location || !data.location.witel_name) ? null : data.location.witel_name;
};

const panelInfo = ref(null);
const panelCategory = ref(null);

const fetchDatatable = () => {
    if(panelCategory.value)
        panelCategory.value.fetch();
};

const onFilterApply = () => {
    if(panelInfo.value)
        panelInfo.value.fetch();
    fetchDatatable();
};

const route = useRoute();
const showDialog = computed(() => route.params.idCategory ? true : false);

const userStore = useUserStore();
const showBtnBack = computed(() => userStore.level != "witel");
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="award" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Gepee Evidence</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Gepee Evidence', 'Witel']" class="ms-4" />
                    </div>
                    <div v-if="showBtnBack" class="col-auto ms-auto">
                        <RouterLink v-if="divreCode" :to="'/gepee-evidence/divre/'+divreCode" class="btn btn-light btn-icon mt-4">
                            <VueFeather type="chevron-left" size="1.2em" class="middle" />
                            <span class="ms-1">Regional</span>
                        </RouterLink>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="p-4">
                <FilterGepeeEvd @apply="onFilterApply" />
            </div>
        </div>
        <div class="container-fluid">
            <SectionGepeeEvidenceInfo ref="panelInfo" @fetched="onInfoFetched" />
        </div>
        <div class="container-fluid">
            <SectionGepeeEvidenceCategory ref="panelCategory" />
        </div>
        <DialogGepeeEvidence v-if="showDialog" @updated="fetchDatatable" />
    </div>
</template>