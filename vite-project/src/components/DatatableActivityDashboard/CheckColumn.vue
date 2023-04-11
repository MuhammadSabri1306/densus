<script setup>
import { toFixedNumber } from "@helpers/number-format";

const props = defineProps({
    type: { type: String, required: true  },
    rowData: { type: Array, required: true }
});
</script>
<template>
    <td v-for="item in rowData" :class="{ 'hover:tw-bg-light position-relative': item.id_schedule }"
        class="text-center middle">
        <span v-if="item.isExists && !item.id_schedule" class="tw-cursor-default">{{ toFixedNumber(item.percent, 2) }}%</span> 
        <RouterLink v-if="item.isExists && item.id_schedule" :to="'/gepee/' + item.id_schedule" class="f-w-700 stretched-link">{{ toFixedNumber(item.percent, 2) }}%</RouterLink> 
    </td>
</template>