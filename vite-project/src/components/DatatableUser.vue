<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useListUserStore } from "@stores/list-user";
import { useUserStore } from "@stores/user";
import { useViewStore } from "@stores/view";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputSwitch from "primevue/inputswitch";
import { FilterMatchMode } from "primevue/api";

const listUserStore = useListUserStore();
const listUser = computed(() => listUserStore.datatable);

await listUserStore.fetchList();

const emit = defineEmits(["view", "edit", "add"]);
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

const isLoading = ref(null);
const viewStore = useViewStore();

const changeIsActive = ({ id, is_active }) => {
    is_active = !is_active;
    const body = { is_active };
    isLoading.value = id;
    
    listUserStore.update(id, body, response => {
        isLoading.value = null;
        if(!response.success)
            return;

        const msg = is_active ? "Berhasil mengaktifkan user." : "Berhasil menonaktifkan user.";
        viewStore.showToast("Aktif User", msg, true);
        listUserStore.fetchList(true);
    });
};

const onCallAction = (emitName, rowData) => {
    showActionOnId.value = 0;
    emit(emitName, rowData);
};

const onDelete = id => {
    if(!confirm("Anda akan menghapus user. Lanjutkan?"))
        return;

    isLoading.value = id;
    listUserStore.delete(id, response => {
        isLoading.value = null;
        if(!response.success)
            return;
            
        viewStore.showToast("Hapus User", "Berhasil menghapus data user.", true);
        listUserStore.fetchList(true);
    });
};
</script>
<template>
    <div>
        <div class="row mb-4">
            <div class="col-6 col-md-4 ms-auto">
                <input type="search" v-model="filter['global'].value" id="userSearch" class="form-control form-control-lg" placeholder="Cari" aria-label="Cari User" />
            </div>
            <div class="col-auto">
                <button type="button" @click="$emit('add')" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">User Baru</span>
                </button>
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
                    <InputSwitch :modelValue="slotProps.data.is_active" @click="changeIsActive(slotProps.data)" :disabled="showActionOnId === slotProps.data.id || isLoading === slotProps.data.id || slotProps.data.isReadonly" />
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
                            <button type="button" @click.stop="onCallAction('edit', slotProps.data)" class="btn-circle btn btn-secondary shadow p-0" :disabled="slotProps.data.isReadonly">
                                <VueFeather type="edit-2" />
                            </button>
                            <button type="button" @click.stop="onDelete(slotProps.data.id)" class="btn-circle btn btn-danger shadow p-0" :disabled="slotProps.data.isReadonly">
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