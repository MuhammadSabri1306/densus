<script setup>
import { toFixedNumber } from "@/helpers/number-format";
import { getPercentageTextClass } from "@/helpers/percentage-color";

const props = defineProps({
    type: { type: String, required: true  },
    rowData: { type: Array, required: true }
});

const isNotSto = item => item.isExists && !item.id_schedule;
const getRoute = idSchedule => "/gepee/" + idSchedule;
</script>
<template>
    <td v-for="item in rowData" :class="{ 'hover:tw-bg-light position-relative': item.id_schedule }"
        class="text-center middle">

        <span v-if="isNotSto(item)" :class="getPercentageTextClass(toFixedNumber(item.percent, 2))"
            class="tw-cursor-default f-w-700">
            {{ toFixedNumber(item.percent, 2) }}%
        </span> 

        <RouterLink v-else-if="item.isExists" :to="getRoute(item.id_schedule)"
            :class="getPercentageTextClass(toFixedNumber(item.percent, 2))"
            class="stretched-link f-w-700">
            {{ toFixedNumber(item.percent, 2) }}%
        </RouterLink>

        <span v-else class="tw-cursor-default f-w-700 text-muted">-</span>

    </td>
</template>