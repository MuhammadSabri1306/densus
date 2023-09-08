<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";

const userStore = useUserStore();
const userRole = computed(() => userStore.role);

const viewStore = useViewStore();
const menuItems = computed(() => {
    const userLevel = userStore.level;
    const userLocId = userStore.locationId;

    if(!userLevel || userLevel == "nasional" || !userLocId)
        return viewStore.menuItems;

    return viewStore.menuItems.map(item => {
        const isGepeeEvidence = item.key == "gepee_evidence";
        const isPue = item.key == "pue";
        
        if(isPue) {
            const pueOnlineIndex = item.child.findIndex(childItem => childItem.key == "online");
            if(pueOnlineIndex >= 0) {
                const newItem = JSON.parse(JSON.stringify(item));
                newItem.child[pueOnlineIndex].to = `${ item.child[pueOnlineIndex].to }/${ userLevel }/${ userLocId }`;
                return newItem;
            }
        }

        if(isGepeeEvidence) {
            const newItem = JSON.parse(JSON.stringify(item));
            newItem.to = `${ item.to }/${ userLevel }/${ userLocId }`;
            return newItem;
        }

        return item;
    });
});

const menuActKeys = computed(() => viewStore.menuActKeys);

const expandedIndexes = ref([]);
const initExpanded = () => {
    for(let index=0; index<menuItems.value.length; index++) {
        if(menuItems.value[index] && menuActKeys.value.length > 0) {
            if(menuItems.value[index].key == menuActKeys.value[0])
                expandedIndexes.value = [index];
        }
    }
};

const toggleExpand = currIndex => {
    let expanded = expandedIndexes.value;
    const index = expanded.indexOf(currIndex);
    if(index < 0)
        expandedIndexes.value = [...expandedIndexes.value, currIndex];
    else
        expandedIndexes.value = expandedIndexes.value.filter(item => item !== currIndex);
};

initExpanded();
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
                            <h6 class="ms-2">
                                <span class="!tw-text-5xl !tw-font-medium">G</span>
                                <span class="!tw-font-bold">ePEE</span>
                            </h6>
                        </div>
                    </li>
                    <li v-for="(item, index) in menuItems" :class="{ 'dropdown': item.child, 'expand': expandedIndexes.indexOf(index) >= 0 }" class="py-1">
                        <RouterLink v-if="!item.child && item.roles.indexOf(userRole) >= 0" :to="item.to" :class="{ 'active': item.key == menuActKeys[0], 'on-development-menu': item.isDev }" class="nav-link">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                        </RouterLink>
                        <a v-if="item.child && item.roles.indexOf(userRole) >= 0" @click="toggleExpand(index)" :class="{ 'active': item.key === menuActKeys[0], 'on-development-menu': item.isDev }" class="nav-link menu-title" role="button">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                            <div class="according-menu">
                                <vue-feather type="chevron-right" />
                            </div>
                        </a>
                        <ul v-if="item.child && item.roles.indexOf(userRole) >= 0" class="nav-submenu menu-content">
                            <li v-for="childItem in item.child">
                                <RouterLink v-if="(childItem.roles && childItem.roles.indexOf(userRole) >= 0) || (!childItem.roles && item.roles.indexOf(userRole) >= 0)" :to="childItem.to" :class="{ 'active': childItem.key === menuActKeys[1] }">{{ childItem.title }}</RouterLink>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>
    </nav>
</template>
<style scoped>

.main-navbar .nav-submenu .active {
    font-weight: 600!important;
}

.on-development-menu::before {
    content: "</>";
    @apply tw-text-[11px] tw-w-6 tw-h-6 tw-inline-flex tw-float-right tw-mr-4
        tw-justify-center tw-items-center tw-bg-danger tw-rounded-full tw-text-white tw-font-semibold;
}

</style>