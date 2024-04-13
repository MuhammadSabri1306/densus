<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useFuelStore } from "@/stores/fuel";
import { useUserStore } from "@/stores/user";
import { groupByLocation } from "@/helpers/location-group";
import { useCollapseRow } from "@/helpers/collapse-row";
import { toFixedNumber, toIdrCurrency } from "@/helpers/number-format";
import Skeleton from "primevue/skeleton";
import DialogGepeeLocationAdd from "@/components/DialogGepeeLocationAdd.vue";

const { collapsedDivre, collapsedWitel, toggleRowCollapse } = useCollapseRow();

const userStore = useUserStore();
const userRole = computed(() => userStore.role);

const fuelStore = useFuelStore();
const filters = ref(fuelStore.filters);
const level = computed(() => {
    const userLevel = userStore.level;

    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const data = ref([]);
const tableData = computed(() => {
    const list = data.value;
    return groupByLocation({
        data: list,
        averagedKeys: ["harga", "ppn", "pph", "ppbkb", "jumlah"],
        divre: {
            groupKey: "divre_kode",
            checker: isMatch => isMatch && level.value == "nasional",
            formatTitle: item => item.divre_name
        },
        witel: {
            groupKey: "witel_kode",
            checker: isMatch => isMatch && level.value != "witel",
            formatTitle: item => "WILAYAH " + item.witel_name
        },
        sto: { formatTitle: item => item.sto_name }
    });
});

const isLoading = ref(true);
const fetch = () => {
    isLoading.value = true;
    fuelStore.getInvoice(response => {
        data.value = response.data
        isLoading.value = false;
    });
};
defineExpose({ fetch });

const showDialogLocationAdd = ref(false);
const router = useRouter();
const onLocationSaved = () => {
    showDialogLocationAdd.value = false;
    isLoading.value = true;
    fuelStore.getInvoice(response => {
        const dataInvoice = response.data;
        const lastInvoiceId = Math.max(...dataInvoice.map(item => Number(item.id)));
        if(lastInvoiceId)
            router.push(`/monitoring-fuel/invoice/${ lastInvoiceId }`);
    });
};
</script>
<template>
    <div>
        <div v-if="!isLoading && tableData.length > 0" class="card card-body">
            <div v-if="userRole == 'admin'" class="d-flex justify-content-end mb-4">
                <button type="button" @click="showDialogLocationAdd = true" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">Lokasi Baru</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-head-primary table-pue">
                    <thead>
                        <tr>
                            <th rowspan="2" class="bg-success">Lingkup Kerja</th>
                            <th colspan="7">Stand Meter</th>
                            <th rowspan="2"></th>
                        </tr>
                        <tr>
                            <th>Harga Solar/liter</th>
                            <th>PPN</th>
                            <th>PPH</th>
                            <th>PPBKB</th>
                            <th>Pembelian Liter BBM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tableData" :class="{ 'row-collapsed': ((item.type == 'sto' && collapsedWitel.indexOf(item.witel_kode) >= 0) || (item.type == 'sto' && collapsedDivre.indexOf(item.divre_kode) >= 0)) || (item.type == 'witel' && collapsedDivre.indexOf(item.divre_kode) >= 0) }">
                            <td>
                                <div v-if="item.type == 'divre'" class="d-flex align-items-center px-3">
                                    <button type="button" @click="toggleRowCollapse('divre', item.divre_kode)" :class="{ 'child-collapsed': collapsedDivre.indexOf(item.divre_kode) >= 0 }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                        <VueFeather type="chevron-right" size="1rem" />
                                    </button>
                                    <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                                </div>
                                <div v-else-if="item.type == 'witel'" class="d-flex align-items-center px-3">
                                    <button type="button" @click="toggleRowCollapse('witel', item.witel_kode)" :class="{ 'child-collapsed': collapsedWitel.indexOf(item.witel_kode) >= 0, 'ms-4': level == 'nasional' }" class="btn btn-circle btn-light p-0 btn-collapse-row">
                                        <VueFeather type="chevron-right" size="1rem" />
                                    </button>
                                    <small class="ms-2 tw-whitespace-nowrap fw-semibold">{{ item.title }}</small>
                                </div>
                                <RouterLink v-else :to="'/monitoring-fuel/invoice/' + item.id_lokasi_gepee" class="d-block px-4 py-1 tw-whitespace-nowrap">
                                    <small :class="{ 'ps-5': level != 'witel', 'ms-5': level == 'nasional' }" class="fw-semibold">{{ item.title }}</small>
                                    <VueFeather type="zoom-in" size="1.2em" class="middle ms-4 icon-detail" />
                                </RouterLink>
                            </td>
                            <td class="text-center middle">{{ toIdrCurrency(item.harga) }}</td>
                            <td class="text-center middle">{{ toFixedNumber(item.ppn, 2) }}</td>
                            <td class="text-center middle">{{ toFixedNumber(item.pph, 2) }}</td>
                            <td class="text-center middle">{{ toFixedNumber(item.ppbkb, 2) }}</td>
                            <td class="text-center middle">{{ toFixedNumber(item.jumlah, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else-if="isLoading" class="card card-body">
            <div class="row g-4">
                <div v-for="n in 15" class="col-6 col-md-4">
                    <Skeleton />
                </div>
            </div>
        </div>
        <div v-else class="px-4 py-3 border">
            <h4 class="text-center">Belum ada data.</h4>
            <div v-if="userRole == 'admin'" class="d-flex justify-content-center">
                <button type="button" @click="showDialogLocationAdd = true" class="btn btn-icon btn-outline-info">
                    <VueFeather type="plus" size="1.2em" />
                    <span class="ms-1">Lokasi Baru</span>
                </button>
            </div>
        </div>
        <DialogGepeeLocationAdd v-if="showDialogLocationAdd" @cancel="" @save="" />
    </div>
</template>
<style scoped>

.icon-detail {
    @apply tw-opacity-0 tw-transition-opacity;
}

a:hover .icon-detail {
    @apply tw-opacity-50;
}

</style>