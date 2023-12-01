<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";

const isResolve = ref(false);
const totalCost = ref(0);
const totalCostValue = computed(() => toIdrCurrency(totalCost.value));

defineExpose({
    resolve: plnData => {
        if(plnData.table && plnData.table.length > 0 && plnData.table[0]["total_biaya"])
            totalCost.value = plnData.table[0]["total_biaya"];
        else
            totalCost.value = 0;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="dollar-sign" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Cost Listrik</span>
                    <h4 class="tw-whitespace-nowrap">Rp {{ totalCostValue }}</h4>
                    <VueFeather type="dollar-sign" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>