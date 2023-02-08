<script setup>
import http from "@/helpers/http-common";

const props = defineProps({
    rtuCode: { required: true }
});

let dataKwh = null;
try {
    let response = await http.get("/monitoring/kwhreal/" + props.rtuCode);
    dataKwh = response.data["kwh_real"];
    console.log(response.data);
} catch(err) {
    console.error(err);
}
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="battery" />
                </div>
                <div class="media-body"><span class="m-0">KwH Saat ini</span>
                    <h4 class="mb-0 counter">{{ dataKwh }}</h4>
                    <VueFeather type="battery" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>