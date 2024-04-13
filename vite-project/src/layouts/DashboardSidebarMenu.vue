<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@/stores/user";
import { useViewStore } from "@/stores/view";

const userStore = useUserStore();
const userRole = computed(() => userStore.role);

const viewStore = useViewStore();

const groupMenuList = menuList => {
    return {
        gepee: menuList.filter(item => item.category == "gepee"),
        oxisp: menuList.filter(item => item.category == "oxisp"),
        management: menuList.filter(item => item.category == "management"),
    }
};

const menuItems = computed(() => {
    const userLevel = userStore.level;
    const userLocId = userStore.locationId;
    const userRolee = userStore.role;
    const menuList = viewStore.menuItems
        .filter(item => {

            if(item.roles.indexOf(userRolee) < 0)
                return false;

            if(Array.isArray(item.child)) {
                item.child = item.child.filter(childItem => {
                    if(!Array.isArray(childItem.roles))
                        return true;
                    return childItem.roles.indexOf(userRolee) >= 0
                });
            }

            return true;

        })
        .map(item => {

            if(!userLevel || userLevel == "nasional" || !userLocId)
                return item;

            const isGepeeEvidence = item.key == "gepee_evidence";
            if(isGepeeEvidence) {
                const newItem = JSON.parse(JSON.stringify(item));
                newItem.to = `${ item.to }/${ userLevel }/${ userLocId }`;
                return newItem;
            }

            return item;

        });

    return groupMenuList(menuList);
});

const menuActKeys = computed(() => viewStore.menuActKeys);

const getRealIndex = (categoryName, index) => {
    const categoryList = ["gepee", "oxisp", "management"];
    const categoryIndex = categoryList.indexOf(categoryName);
    if(categoryIndex <= 0)
        return index;

    for(let i=0; i<categoryIndex; i++) {
        index += menuItems.value[categoryList[i]].length;
    }
    return index;
};

const expandedIndexes = ref([]);
const initExpanded = () => {
    Object.entries(menuItems.value).forEach(([ categoryName, menuItem ]) => {
        let realIndex = -1;
        for(let index=0; index<menuItem.length; index++) {
            realIndex = getRealIndex(categoryName, index);
            if(menuItem[index] && menuActKeys.value.length > 0) {
                if(menuItem[index].key == menuActKeys.value[0])
                    expandedIndexes.value = [realIndex];
            }
        }
    });
};

const toggleExpand = (categoryName, currIndex) => {
    currIndex = getRealIndex(categoryName, currIndex);
    let expanded = expandedIndexes.value;
    const index = expanded.indexOf(currIndex);

    if(index < 0)
        expandedIndexes.value = [...expandedIndexes.value, currIndex];
    else
        expandedIndexes.value = expandedIndexes.value.filter(item => item !== currIndex);
};

initExpanded();

const dropdownClass = (categoryName, itemChild, index) => {
    index = getRealIndex(categoryName, index);
    return {
        'dropdown': itemChild,
        'expand': expandedIndexes.value.indexOf(index) >= 0
    };
};

const dropdownItemClass = (itemKey, itemIsDev) => {
    return {
        'active': itemKey == menuActKeys.value[0],
        'on-development-menu': itemIsDev
    };
};

const dropdownItemChildClass = itemKey => {
    return { 'active': itemKey === menuActKeys.value[1] };
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
                    <li v-if="menuItems.gepee.length > 0" class="sidebar-main-title">
                        <div>
                            <h6 class="ms-2">Menu GePEE</h6>
                        </div>
                    </li>
                    <li v-for="(item, index) in menuItems.gepee" :class="dropdownClass('gepee', item.child, index)" class="py-1">
                        <RouterLink v-if="!item.child" :to="item.to" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                        </RouterLink>
                        <a v-else @click="toggleExpand('gepee',index)" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link menu-title" role="button">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                            <div class="according-menu">
                                <vue-feather type="chevron-right" />
                            </div>
                        </a>
                        <ul v-if="item.child" class="nav-submenu menu-content">
                            <li v-for="childItem in item.child">
                                <RouterLink :to="childItem.to" :class="dropdownItemChildClass(childItem.key)">
                                    {{ childItem.title }}
                                </RouterLink>
                            </li>
                        </ul>
                    </li>
                    <li v-if="menuItems.oxisp.length > 0" class="sidebar-main-title">
                        <div>
                            <h6 class="ms-2">Menu OX ISP</h6>
                        </div>
                    </li>
                    <li v-for="(item, index) in menuItems.oxisp" :class="dropdownClass('oxisp', item.child, index)" class="py-1">
                        <RouterLink v-if="!item.child" :to="item.to" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                        </RouterLink>
                        <a v-else @click="toggleExpand('oxisp', index)" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link menu-title" role="button">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                            <div class="according-menu">
                                <vue-feather type="chevron-right" />
                            </div>
                        </a>
                        <ul v-if="item.child" class="nav-submenu menu-content">
                            <li v-for="childItem in item.child">
                                <RouterLink :to="childItem.to" :class="dropdownItemChildClass(childItem.key)">
                                    {{ childItem.title }}
                                </RouterLink>
                            </li>
                        </ul>
                    </li>
                    <li v-if="menuItems.management.length > 0" class="sidebar-main-title">
                        <div>
                            <h6 class="ms-2">Menu Management</h6>
                        </div>
                    </li>
                    <li v-for="(item, index) in menuItems.management" :class="dropdownClass('management', item.child, index)" class="py-1">
                        <RouterLink v-if="!item.child" :to="item.to" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                        </RouterLink>
                        <a v-else @click="toggleExpand('management', index)" :class="dropdownItemClass(item.key, item.isDev)"
                            class="nav-link menu-title" role="button">
                            <vue-feather :type="item.icon" size="1.2rem" class="me-2" />
                            <span>{{ item.title }}</span>
                            <div class="according-menu">
                                <vue-feather type="chevron-right" />
                            </div>
                        </a>
                        <ul v-if="item.child" class="nav-submenu menu-content">
                            <li v-for="childItem in item.child">
                                <RouterLink :to="childItem.to" :class="dropdownItemChildClass(childItem.key)">
                                    {{ childItem.title }}
                                </RouterLink>
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