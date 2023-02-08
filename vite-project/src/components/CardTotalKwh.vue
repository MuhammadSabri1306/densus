<script setup>
import http from "@/helpers/http-common";

const props = defineProps({
    rtuCode: { required: true }
});

let dataKwhTotal = null;
try {
    const response = await http.get("/monitoring/kwhtotal/" + props.rtuCode);
    dataKwhTotal = response.data["kwh_total"]["kwh_value"];
} catch(err) {
    console.error(err);
}
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <!-- <i data-feather="battery-charging"></i> -->
                    <VueFeather type="battery-charging" />
                </div>
                <div class="media-body">
                    <span class="m-0">KwH Total Feb 2023</span>
                    <h4 class="mb-0 counter">{{ dataKwhTotal }}</h4>
                    <VueFeather type="battery-charging" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>