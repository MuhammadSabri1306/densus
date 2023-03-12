<script setup>
import { computed } from "vue";
import { useActivityStore } from "@stores/activity";

const props = defineProps({
    rowData: { type: Array, required: true }
});

const data = computed(() => props.rowData);
const activityStore = useActivityStore();

const onCheckboxChange = cbData => {
    if(!cbData.schedule) {
        activityStore.addScheduleItem(cbData.category.id, cbData.location.id);
        return;
    }
    
    if(cbData.schedule.id) {
        activityStore.updateSheduleItem(cbData.schedule.id);
        return;
    }

    if(cbData.schedule.value == 1)
        activityStore.removeScheduleItem(cbData.category.id, cbData.location.id);
    else
        activityStore.addScheduleItem(cbData.category.id, cbData.location.id);
};

const availableMonth = computed(() => activityStore.availableMonth);
const isDisabled = item => {
    let hasData = false;
    let isCurrMonth = true;

    if(item.schedule) {

        hasData = item.schedule.execution_count > 0;
        if(item.month && item.month.number)
            isCurrMonth = item.month.number == item.schedule.createdAt.getMonth() + 1;

    } else if(item.month && item.month.number) {

        isCurrMonth = item.month.number == availableMonth.value;

    }
    return hasData || !isCurrMonth;
};
</script>
<template>
    <td v-for="item in data" class="text-center middle">
        <input type="checkbox" :checked="item.schedule && item.schedule.value == 1" @change.prevent="onCheckboxChange(item)"
            :disabled="isDisabled(item)" :aria-label="item.category?.activity + ' bulan ' + item.month?.number" class="form-check-input" />
    </td>
</template>