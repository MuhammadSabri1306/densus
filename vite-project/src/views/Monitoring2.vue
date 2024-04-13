<script setup>
import DashboardBreadcrumb from "@/layouts/DashboardBreadcrumb.vue";
</script>
<template>
    <div>
        <div class="container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>
                            <VueFeather type="airplay" size="1.2em" class="font-primary middle" />
                            <span class="middle ms-3">Monitoring Energy</span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="mb-0">
                                <VueFeather type="activity" size="1.2em" class="font-primary middle" />
                                Monitoring PLN
                            </h5>
                        </div>
                        <div class="card-body d-grid">
                            <RouterLink to="/monitoring-pln/params" class="btn btn-lg btn-success mb-3">Data Parameter</RouterLink>
                            <RouterLink to="/monitoring-pln/billing" class="btn btn-lg btn-success">Data Bill</RouterLink>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="mb-0">
                                <VueFeather type="activity" size="1.2em" class="font-primary middle" />
                                Monitoring Fuel
                            </h5>
                        </div>
                        <div class="card-body d-grid">
                            <RouterLink to="/monitoring-fuel/params" class="btn btn-lg btn-success mb-3">Data Parameter</RouterLink>
                            <RouterLink to="/monitoring-fuel/invoice" class="btn btn-lg btn-success">Data Invoice</RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>