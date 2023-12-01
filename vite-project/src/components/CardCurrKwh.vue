<script setup>
import { ref, computed } from "vue";
import { toIdrCurrency } from "@helpers/number-format";

const isResolve = ref(false);
const kwh = ref(null);
const kwhValue = computed(() => !kwh.value ? kwh.value : kwh.value.toString().replace(".", ","));

defineExpose({
    resolve: dataKwh => {
        kwh.value = dataKwh;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card o-hidden border-0">
        <div class="bg-primary b-r-4 card-body">
            <div class="media static-top-widget">
                <div class="align-self-center text-center">
                    <VueFeather type="battery" />
                </div>
                <div class="media-body"><span class="m-0">KwH Saat ini</span>
                    <h4 class="mb-0 counter">{{ toIdrCurrency(kwhValue) }}</h4>
                    <VueFeather type="battery" class="icon-bg" />
                </div>
            </div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>