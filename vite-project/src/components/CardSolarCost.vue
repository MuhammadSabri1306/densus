<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const dataBbmCost = await monitoringStore.getBbmCost(props.rtuCode);
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="zap-off" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Biaya Solar Genset</span>
                    <h4 class="tw-whitespace-nowrap">Rp {{ toIdrCurrency(dataBbmCost) }}</h4>
                    <VueFeather type="zap-off" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>