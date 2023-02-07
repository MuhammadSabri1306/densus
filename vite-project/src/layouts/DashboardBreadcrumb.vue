<script setup>
import { computed } from "vue";

const props = defineProps({
    items: { type: Array, default: [] }
});

const listItem = computed(() => {
    return props.items.map(item => {
        if(typeof item == "string")
            return { title: item };
        return item;
    });
});
</script>
<template>
    <ol class="breadcrumb">
        <li v-for="(item, index) in listItem" :class="{ 'active': index === listItem.length-1 }" class="breadcrumb-item">
            <RouterLink v-if="item.route">{{ item.title }}</RouterLink>
            <span v-else>{{ item.title }}</span>
        </li>
    </ol>
</template>