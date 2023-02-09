<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputSwitch from "primevue/inputswitch";
import { FilterMatchMode } from "primevue/api";

const listUser = ref([
    {
        no: 1,
        id: 1,
        username: "@sabri13",
        nama: "Muhammad Sabri",
        organisasi: "nasional",
        role: "admin",
        no_hp: "085144392944",
        email: "muhammadsabri1306@gmail.com",
        is_ldap: false,
        telegram_id: null,
        telegram_username: null,
        witel: "-",
        divre: "-",
        is_active: true
    },
    {
        no: 2,
        id: 2,
        username: "dev@13",
        nama: "Ahmad",
        organisasi: "divre",
        role: "teknisi",
        no_hp: "082323465422",
        email: "test@gmail.com",
        is_ldap: true,
        telegram_id: null,
        telegram_username: null,
        witel: "-",
        divre: "DIVISI TELKOM REGIONAL VII",
        is_active: true
    }
]);

const emit = defineEmits(["view", "edit", "delete"]);
const filter = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const showActionOnId = ref(0);
const hideDtAction = () => showActionOnId.value = 0;
const toggleDtAction = id => {
    if(showActionOnId.value === 0)
        showActionOnId.value = id;
    else
        showActionOnId.value = 0;
};

onMounted(() => document.body.addEventListener("click", hideDtAction));
onUnmounted(() => document.body.addEventListener("click", hideDtAction));

const changeIsActive = id => {
    console.log(id);
};

const onCallAction = (emitName, rowData) => {
    showActionOnId.value = 0;
    emit(emitName, rowData);
};
</script>
<template>
    <div>
        <div class="row mb-4">
            <div class="col-6 col-md-4 ms-auto">
                <input type="search" v-model="filter['global'].value" id="userSearch" class="form-control form-control-lg" placeholder="Cari" aria-label="Cari User" />
            </div>
        </div>
        <DataTable :value="listUser" showGridlines :paginator="true" :rows="10"
            v-model:filters="filter" dataKey="id" responsiveLayout="scroll" stateStorage="session"
            stateKey="dt-state-user" class="table-sm">
            <Column field="no" header="No" :sortable="true" />
            <Column field="username" header="Username" :sortable="true" />
            <Column field="nama" header="Nama" :sortable="true" />
            <Column field="organisasi" header="Organisasi" :sortable="true">
                <template #body="slotProps">
                    <p class="text-center text-sm text-uppercase">{{ slotProps.data.organisasi }}</p>
                </template>
            </Column>
            <Column field="role" header="Role" :sortable="true">
                <template #body="slotProps">
                    <p class="text-center text-sm text-uppercase">{{ slotProps.data.role }}</p>
                </template>
            </Column>
            <Column field="is_ldap" header="LDAP">
                <template #body="slotProps">
                    <div class="text-center">
                        <span v-if="slotProps.data.is_ldap" class="span-circle bg-primary">
                            <VueFeather type="check" size="1rem" class="text-white" />
                        </span>
                        <span v-else class="span-circle bg-danger">
                            <VueFeather type="x" size="1rem" class="text-white" />
                        </span>
                    </div>
                </template>
            </Column>
            <Column field="is_active" header="Aktif">
                <template #body="slotProps">
                    <InputSwitch :value="slotProps.data.is_active" @click="changeIsActive(slotProps.data.id)" :disabled="showActionOnId === slotProps.data.id" />
                </template>
            </Column>
            <Column header="Action">
                <template #body="slotProps">
                    <div class="d-flex">
                        <button type="button" class="btn-circle btn-dt-action" @click.stop="toggleDtAction(slotProps.data.id)">
                            <VueFeather type="chevron-left" size="1rem" class="me-1" />
                        </button>
                        <div :class="{ 'show': showActionOnId === slotProps.data.id }" class="dt-dropdown">
                            <button type="button" @click.stop="onCallAction('view', slotProps.data)" class="btn-circle btn btn-primary shadow p-0">
                                <VueFeather type="eye" />
                            </button>
                            <button type="button" @click.stop="onCallAction('edit', slotProps.data)" class="btn-circle btn btn-secondary shadow p-0">
                                <VueFeather type="edit-2" />
                            </button>
                            <button type="button" @click.stop="onCallAction('delete', slotProps.data)" class="btn-circle btn btn-danger shadow p-0">
                                <VueFeather type="trash" />
                            </button>
                        </div>
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
<style scoped>

.btn-dt-action {
    @apply tw-bg-transparent hover:tw-bg-gray-200 focus:tw-ring-primary/60;
}

.dt-dropdown {
    @apply tw-relative;
}

.dt-dropdown > button {
    @apply tw-absolute tw-top-0 tw-right-[-100%] tw-opacity-0 tw-w-0 tw-z-[22];
    transition: right 0.3s, opacity 0.3s, width 0.3s;
}

.dt-dropdown.show > button {
    @apply tw-opacity-100 tw-w-10;
}

.dt-dropdown.show > button:first-child {
    right: calc(100% + 9rem);
}

.dt-dropdown.show > button:nth-child(2) {
    right: calc(100% + 6rem);
}

.dt-dropdown.show > button:nth-child(3) {
    right: calc(100% + 3rem);
}

</style>