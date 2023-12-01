<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";

const isResolve = ref(false);
const bbmCost = ref(null);
const bbmCostValue = computed(() => !bbmCost.value ? bbmCost.value : toIdrCurrency(bbmCost.value));

defineExpose({
    resolve: cost => {
        bbmCost.value = cost;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="zap-off" />
                </div>
                <div class="media-body">
                    <span class="m-0">Est Biaya Solar Genset</span>
                    <h4 class="tw-whitespace-nowrap">Rp {{ bbmCostValue }}</h4>
                    <VueFeather type="zap-off" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>