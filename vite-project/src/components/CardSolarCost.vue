<script setup>
import http from "@/helpers/http-common";

const props = defineProps({
    rtuCode: { required: true }
});

let dataBbmCost = null;
try {
    let response =  await http.get("/monitoring/costbbm/" + props.rtuCode);
    dataBbmCost = response.data["bbm_cost"] || 0;
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
                    <h4>Rp {{ dataBbmCost }}</h4>
                    <VueFeather type="zap-off" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>