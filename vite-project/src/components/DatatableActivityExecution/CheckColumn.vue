<script setup>
import { computed } from "vue";
import { useUserStore } from "@stores/user";

const props = defineProps({
    rowData: { type: Array, required: true }
});

const userStore = useUserStore();
const isAdmin = computed(() => userStore.role == "admin");
</script>
<template>
    <td v-for="item in rowData" :class="{ 'exec-exists': item.schedule && item.schedule.execution_count > 0, 'exec-not-exists':
        item.schedule && item.schedule.execution_count < 1 }" class="text-center middle">

        <input type="checkbox" v-if="item.schedule" :checked="item.schedule.execution_count > 0"
            @click.prevent="$router.push('/gepee/exec/' + item.schedule.id)" :disabled="!isAdmin && item.schedule.is_enabled == 0"
            :aria-label="item.category?.activity + ' bulan ' + item.month?.number" class="form-check-input" />
            
    </td>
</template>
<style scoped>

td.exec-exists {
    background-color: #6e9b94;
    /* background-color: rgba(114,169,67,0.67); */
}

td.exec-not-exists {
    background-color: #f57575;
    /* background-color: rgba(210,45,61,0.9); */
}

</style>