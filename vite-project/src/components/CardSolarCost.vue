<script setup>
import http from "@/helpers/http-common";
import { toIdrCurrency } from "@helpers/number-format";

const props = defineProps({
    rtuCode: { required: true }
});

let dataBbmCost = 0;
try {
    let response =  await http.get("/monitoring/costbbm/" + props.rtuCode);
    const wrapper = response.data["bbm_cost"];
    if(wrapper && wrapper.bbm_cost)
        dataBbmCost = wrapper.bbm_cost;
    console.log(dataBbmCost)
} catch(err) {
    console.error(err);
}
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