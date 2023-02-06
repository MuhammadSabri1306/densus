<script setup>
import { computed } from "vue";
import { useViewStore } from "@stores/view";

const viewStore = useViewStore();
const menuItems = viewStore.menuItems;
const menuActKeys = computed(() => viewStore.menuActKeys);

const menuExpanded = computed(() => viewStore.menuExpanded);
const setMenuExpanded = index => {
    if(index === menuExpanded.value)
        index = -1;
    viewStore.setMenuExpanded(index);
};
</script>
<template>
    <nav>
        <div class="main-navbar">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="mainnav">
                <ul class="nav-menu custom-scrollbar">
                    <li class="back-btn">
                        <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                    </li>
                    <li class="sidebar-main-title">
                        <div>
                            <h6>Saving Energy</h6>
                        </div>
                    </li>
                    <li v-for="(item, index) in menuItems" :class="{ 'dropdown': item.child, 'expand': index === menuExpanded || item.key === menuActKeys[0] }">
                        <RouterLink v-if="!item.child" :to="item.to" :class="{ 'active': item.key == menuActKeys[0] }" class="nav-link">
                            <vue-feather :type="item.icon" />
                            <span>{{ item.title }}</span>
                        </RouterLink>
                        <a v-else @click="setMenuExpanded(index)" :class="{ 'active': item.key === menuActKeys[0] }" class="nav-link menu-title" role="button">
                            <vue-feather :type="item.icon" />
                            <span>{{ item.title }}</span>
                            <div class="according-menu">
                                <vue-feather type="chevron-right" />
                            </div>
                        </a>
                        <ul v-if="item.child" class="nav-submenu menu-content">
                            <li v-for="childItem in item.child">
                                <RouterLink :to="childItem.to" :class="{ 'active': childItem.key === menuActKeys[1] }">{{ childItem.title }}</RouterLink>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>
    </nav>
</template>