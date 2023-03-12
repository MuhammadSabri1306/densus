<script setup>
import { toFixedNumber } from "@helpers/number-format";

const props = defineProps({
    rowData: { type: Array, required: true }
});
</script>
<template>
    <td v-for="item in rowData" :class="{ 'hover:tw-bg-light position-relative': item.schedule && item.schedule.id }" class="text-center middle">
        <span v-if="item.consistency !== undefined && !item.schedule.id" class="tw-cursor-default">{{ toFixedNumber(item.consistency, 2) }}%</span> 
        <RouterLink v-if="item.consistency !== undefined && item.schedule.id" :to="'/gepee/'+item.schedule.id" class="f-w-700 stretched-link">{{ toFixedNumber(item.consistency, 2) }}%</RouterLink> 
    </td>
</template>