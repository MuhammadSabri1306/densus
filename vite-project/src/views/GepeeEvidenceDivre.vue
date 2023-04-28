<script setup>
import { ref } from "vue";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import SectionGepeeEvidenceInfo from "@components/SectionGepeeEvidenceInfo.vue";
import SectionGepeeEvidenceGrid from "@components/SectionGepeeEvidenceGrid.vue";
import FilterGepeeEvd from "@components/FilterGepeeEvd.vue";

const panelInfo = ref(null);
const panelGrid = ref(null);
const onFilterApply = () => {
    if(panelInfo.value)
        panelInfo.value.fetch();
    if(panelGrid.value)
        panelGrid.value.fetch();
};
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
                        <DashboardBreadcrumb :items="['Gepee Evidence', 'Regional']" class="ms-4" />
                    </div>
                    <div class="col-auto ms-auto">
                        <RouterLink to="/gepee-evidence" class="btn btn-light btn-icon mt-4">
                            <VueFeather type="chevron-left" size="1.2em" class="middle" />
                            <span class="ms-1">Nasional</span>
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
            <SectionGepeeEvidenceInfo ref="panelInfo" />
        </div>
        <div class="container-fluid">
            <SectionGepeeEvidenceGrid ref="panelGrid" />
        </div>
    </div>
</template>