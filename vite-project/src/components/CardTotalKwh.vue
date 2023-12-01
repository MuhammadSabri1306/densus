<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";

const isResolve = ref(false);
const kwhTotal = ref(null);
const kwhTotalValue = computed(() => !kwhTotal.value ? kwhTotal.value : toIdrCurrency(kwhTotal.value));


const monthKeys = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
const currDate = new Date();
const currMonth = currDate.getMonth() + 1;
const currMonthTitle = monthKeys[currMonth - 1];
const currYear = currDate.getFullYear();

defineExpose({
    resolve: dataKwhTotal => {
        kwhTotal.value = dataKwhTotal;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card o-hidden border-0">
        <div class="bg-secondary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <!-- <i data-feather="battery-charging"></i> -->
                    <VueFeather type="battery-charging" />
                </div>
                <div class="media-body">
                    <span class="m-0">KwH Total {{ currMonthTitle }} {{ currYear }}</span>
                    <h4 class="mb-0 counter">{{ kwhTotalValue }}</h4>
                    <VueFeather type="battery-charging" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>