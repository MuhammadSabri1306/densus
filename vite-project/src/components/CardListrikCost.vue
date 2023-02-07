<script setup>
import http from "@/helpers/http-common";

const props = defineProps({
    rtuCode: { required: true }
});

let dataTotalCost = null;
try {
    
    let response = await http.get("/monitoring/tabledata/" + props.rtuCode);
    const totalCost = response.data["tabledata"].table[0]["total_biaya"];
    dataTotalCost = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR"
    }).format(totalCost);

} catch(err) {
    console.error(err);
}
</script>
<template>
    <div class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="dollar-sign" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Cost Listrik</span>
                    <h4>Rp {{ dataTotalCost }}</h4>
                    <VueFeather type="dollar-sign" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
</template>