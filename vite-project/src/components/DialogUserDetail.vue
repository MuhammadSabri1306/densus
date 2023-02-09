<script setup>
import Dialog from "primevue/dialog";

const props = defineProps({
    data: Object
});
</script>
<template>
    <Dialog header="Detail User" modal maximizable draggable dismissableMask>
        <div class="p-4">
            <div class="tw-grid tw-grid-cols-[auto_1fr] tw-gap-x-2 tw-gap-y-4">
                <b>Nama</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.nama }}</span>
                </div>
                <b>Username</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.username }}</span>
                </div>
                <b>Role</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break text-uppercase">{{ data.role }}</span></div>
                <b>Organisasi</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break text-uppercase">{{ data.organisasi }}</span></div>
                <b>Divre</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.divre || "-" }}</span></div>
                <b>Witel</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.witel || "-" }}</span></div>
                <b>LDAP</b>
                <div class="d-flex">
                    <span class="me-2">:</span>
                    <span :class="{ 'bg-primary': data.is_ldap, 'bg-danger': !data.is_ldap }" class="span-circle">
                        <VueFeather v-if="data.is_ldap" type="check" size="1rem" class="text-white" />
                        <VueFeather v-else type="x" size="1rem" class="text-white" />
                    </span>
                </div>
                <b>Email</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <a :href="'mailto:'+data.email" target="_blank" class="text-break">{{ data.email }}</a>
                </div>
                <b class="d-md-none">No. Hp</b>
                <b class="d-none d-md-inline">No. Handphone</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <a :href="'tel:'+data.no_hp" class="text-break">{{ data.no_hp }}</a>
                </div>
                <b>Telegram Id</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.telegram_id || "-" }}</span>
                </div>
                <b class="d-md-none">Telegram<br>Username</b>
                <b class="d-none d-md-inline">Telegram Username</b>
                <div class="d-flex">
                    <span class="me-1">:</span>
                    <span class="text-break">{{ data.telegram_username || "-" }}</span>
                </div>
                <b>Status Akun</b>
                <div class="d-flex">
                    <span class="me-2">:</span>
                    <span v-if="data.is_active" class="px-2 py-1 bg-primary rounded-pill">Aktif <VueFeather type="check" size="0.9em" class="mt-1 ms-1" /></span>
                    <span v-else class="px-2 py-1 bg-danger rounded-pill">Tidak Aktif <VueFeather type="x" size="0.9em" class="mt-1 ms-1" /></span>
                </div>
            </div>
        </div>
    </Dialog>
</template>