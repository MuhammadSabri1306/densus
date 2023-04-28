<script setup>
import { ref, onMounted } from "vue";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import FilterPln from "@components/FilterPln.vue";
import DatatableFuelInvoice from "@components/DatatableFuelInvoice.vue";

const tableInvoice = ref(null);
const onFilterSubmit = () => {
    tableInvoice.value && tableInvoice.value.fetch();
};

const isFirstFetch = ref(true);
onMounted(() => {
    if(!isFirstFetch.value)
        return;
    tableInvoice.value && tableInvoice.value.fetch();
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
                            <span class="middle ms-3">Data Invoice</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Monitoring Fuel', 'Invoice']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <FilterPln @submit="onFilterSubmit" />
        </div>
        <DatatableFuelInvoice ref="tableInvoice" class="container-fluid" />
    </div>
</template>