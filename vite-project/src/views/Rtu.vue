<script setup>
import Skeleton from "primevue/skeleton";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";
import DatatableRtuList from "@components/DatatableRtuList.vue";
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="hard-drive" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">List RTU Tersedia</span>
                        </h3>
                        <DashboardBreadcrumb :items="['Master RTU', 'RTU Tersedia']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="card">
                <div class="card-body">
                    <Suspense>
                        <DatatableRtuList />
                        <template #fallback>
                            <div class="m-t-30">
                                <div v-for="x in 4" class="row mb-4">
                                    <div v-for="x in 4" class="col"><Skeleton height="2rem" /></div>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </div>
            </div>
        </div>
    </div>
</template>