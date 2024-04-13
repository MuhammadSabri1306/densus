<script setup>
import { ref, onMounted } from "vue";
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
import FilterPln from "@/components/FilterPln.vue";
import DatatablePlnBilling from "@/components/DatatablePlnBilling.vue";

const tableBilling = ref(null);
const onFilterSubmit = () => {
    tableBilling.value && tableBilling.value.fetch();
};

const isFirstFetch = ref(true);
onMounted(() => {
    if(!isFirstFetch.value)
        return;
    tableBilling.value && tableBilling.value.fetch();
});
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="activity" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Data Bill PLN</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring PLN', 'Bill']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <FilterPln @submit="onFilterSubmit" />
        </div>
        <DatatablePlnBilling ref="tableBilling" class="container-fluid" />
    </div>
</template>