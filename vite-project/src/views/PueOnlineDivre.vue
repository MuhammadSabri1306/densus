<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { usePueV2Store } from "@stores/pue-v2";
import DashboardBreadcrumb from "@layouts/DashboardBreadcrumb.vue";

import CardPueRtuInfo from "@components/CardPueRtuInfo.vue";
import CardPueCurrent from "@components/CardPueCurrent.vue";
import CardPueMax from "@components/CardPueMax.vue";
import ChartPueValue from "@components/ChartPueValue.vue";
import CardContentPueAverage from "@components/CardContentPueAverage.vue";
import CardContentPuePerformance from "@components/CardContentPuePerformance.vue";
import DatatablePueOnSto from "@components/DatatablePueOnSto.vue";

const route = useRoute();
const divreCode = computed(() => route.params.divreCode);
const location = ref(null);

const pueStore = usePueV2Store();
pueStore.setCurrentZone({ divre: divreCode.value });
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
                        <DashboardBreadcrumb :items="['Monitoring PUE', 'PUE Online', 'Regional']" class="ms-4" />
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid dashboard-default-sec">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <CardPueRtuInfo :data="location" />
                    <CardPueCurrent @loaded="loc => location = loc" />
                    <CardPueMax title="Nilai Rata-Rata PUE tertinggi tahun ini" />
                </div>
                <div class="col-md-8">
                    <ChartPueValue />
                </div>
                <div class="col-md-6">
                    <CardContentPueAverage />
                </div>
                <div class="col-md-6">
                    <CardContentPuePerformance />
                </div>
                <div class="col-12">
                    <DatatablePueOnSto level="nasional" />
                </div>
            </div>
        </div>
    </div>
</template>