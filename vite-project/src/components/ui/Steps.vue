<script setup>
import { computed } from "vue";

const emit = defineEmits(["navigate"]);
const props = defineProps({
    steps: { type: Array, required: true },
    currPage: { type: Number, default: 1 }
});

const lists = computed(() => {
    const stepList = props.steps;
    const activeNumber = props.currPage;

    return stepList.map((title, index) => {
        const pageNumber = index + 1;
        const isActive = pageNumber === activeNumber;
        return { pageNumber, isActive, title };
    });
});
</script>
<template>
    <nav class="steps">
        <ol class="steps-list">
            <li v-for="item in lists" class="steps-item">
                <template v-if="item.isActive">
                    <a role="button" @click="$emit('navigate', item.pageNumber)" class="step-menuitem step-active" aria-current="step">
                        <span class="steps-number">{{ item.pageNumber }}</span>
                        <span class="steps-title">{{ item.title }}</span>
                    </a>
                </template>
                <template v-else>
                    <a role="button" @click="$emit('navigate', item.pageNumber)" class="step-menuitem">
                        <span class="steps-number">{{ item.pageNumber }}</span>
                        <span class="steps-title">{{ item.title }}</span>
                    </a>
                </template>
            </li>
        </ol>
    </nav>
</template>
<style scoped>

.steps {
    position: relative;
}

.steps .steps-list {
    padding: 0;
    margin: 0;
    list-style-type: none;
    display: flex;
}

.steps-item {
    position: relative;
    display: flex;
    justify-content: center;
    flex: 1 1 auto;
}

.steps-item:before {
    content: " ";
    border-top: 1px solid #dee2e6;
    width: 100%;
    top: 50%;
    left: 0;
    display: block;
    position: absolute;
    margin-top: -1rem;
}

.steps-item .step-menuitem {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    text-decoration: none;
    border-radius: 6px;
    background: #ffffff;
    transition: box-shadow 0.2s;
}

.steps-item .step-menuitem:focus {
    outline: 0 none;
    outline-offset: 0;
    box-shadow: 0 0 0 0.2rem #99F6E4;
}

.step-menuitem.step-active {
    cursor: default;
}

.steps-number {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #495057;
    border: 1px solid #e9ecef;
    background: #ffffff;
    min-width: 2rem;
    height: 2rem;
    line-height: 2rem;
    font-size: 1.143rem;
    z-index: 1;
    border-radius: 50%;
}

.step-menuitem.step-active .steps-number {
    background: #F0FDFA;
    color: #0F766E;
}

.steps-title {
    white-space: nowrap;
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
}

.step-menuitem.step-active .steps-title {
    font-weight: 700;
    color: #495057;
}
</style>