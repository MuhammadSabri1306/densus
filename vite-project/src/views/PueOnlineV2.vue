<script setup>
import { ref, computed } from "vue";
import { useViewStore } from "@/stores/view";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import FilterGepeeV2 from "@/components/FilterGepeeV2.vue";
import SectionPueOnline from "@/components/SectionPueOnline/index.vue";

const viewStore = useViewStore();
const sectionPueElm = ref(null);
const filterAutoApply = () => viewStore.filters.divre ? true : false;
const onFilterApply = filterValue => {
    viewStore.setFilter(filterValue);
    sectionPueElm.value.fetch();
};
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="feather" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">PUE Online</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PUE & IKE', 'PUE Online']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <FilterGepeeV2 @apply="onFilterApply" :autoApply="filterAutoApply" divreColClass="col-12 col-md"
                witelColClass="col-12 col-md" />
        </div>
        <div class="container-fluid dashboard-default-sec">
            <SectionPueOnline ref="sectionPueElm" />
        </div>
    </div>
</template>