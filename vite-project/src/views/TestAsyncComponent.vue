<script setup>
import { AsyncWrapper } from "@/components/AsyncWrapper";

const compLoader = () => import("@/components/FilterGepeeV2.vue");
const onCompResolve = () => console.log("resolve");

const onFilterApply = () => console.log("apply");
</script>
<template>
    <main>
        <div class="container-fluid my-5">
            <AsyncWrapper :loader="compLoader" @resolve="onCompResolve" class="test"
                :useMonth="true" @apply="onFilterApply">
                <template #loading>
                    <p class="text-center">Loading</p>
                </template>
                <template #error>
                    <p class="text-center text-danger">Error</p>
                </template>
            </AsyncWrapper>
        </div>
        <div class="container-fluid my-5">
            <AsyncWrapper :loader="compLoader" isAsyncSetup @resolve="onCompResolve" :useMonth="true" @apply="onFilterApply">
                <template #loading>
                    <p class="text-center">Loading</p>
                </template>
                <template #error>
                    <p class="text-center text-danger">Error</p>
                </template>
            </AsyncWrapper>
        </div>
    </main>
</template>