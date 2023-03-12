<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
let dataKwh = await monitoringStore.getKwhReal(props.rtuCode);
dataKwh = dataKwh.toString().replace(".", ",");
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="battery" />
                </div>
                <div class="media-body"><span class="m-0">KwH Saat ini</span>
                    <h4 class="mb-0 counter">{{ toIdrCurrency(dataKwh) }}</h4>
                    <VueFeather type="battery" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>