<script setup>
import { computed } from "vue";

const props = defineProps({
    rowData: { type: Array, required: true }
});

const data = computed(() => props.rowData);

const getTdClass = item => {
    if(!item.isExists)
        return;

    const { execCount, apprCount } = item;

    if(execCount < 1)
        return "activity-bg-danger";
    if(apprCount < execCount)
        return "activity-bg-warning";
    return "activity-bg-success";
};
</script>
<template>
    <td v-for="item in data" :class="getTdClass(item)" class="text-center middle">
        <input type="checkbox" v-if="item.isExists" :checked="item.isChecked" @click.prevent="$router.push('/gepee/exec/' + item.scheduleId)"
            :disabled="item.isDisabled" :aria-label="item.label" class="form-check-input" />
    </td>
</template>
<style scoped>

td.activity-bg-success {
    background-color: #2cb198!important;
}

td.activity-bg-warning {
    background-color: #ffe55c!important;
}

td.activity-bg-danger {
    background-color: #ff658d!important;
}

td.exec-exists {
    background-color: #6e9b94;
    /* background-color: rgba(114,169,67,0.67); */
}

td.exec-not-exists {
    background-color: #f57575;
    /* background-color: rgba(210,45,61,0.9); */
}

</style>