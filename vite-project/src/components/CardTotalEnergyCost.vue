<script setup>
import { useMonitoringStore } from "@stores/monitoring";
import { toIdrCurrency } from "@helpers/number-format";

const props = defineProps({
    rtuCode: { required: true }
});

const monitoringStore = useMonitoringStore();
const dataBbmCost = await monitoringStore.getBbmCost(props.rtuCode);
const tableData = await monitoringStore.getTableData(props.rtuCode);

let dataTotalCost;
try {
    dataTotalCost = tableData.table[0]["total_biaya"];
} catch(e) {
    dataTotalCost = 0;
}

const dataEnergyCost = dataBbmCost + dataTotalCost;
</script>
<template>
    <div class="card income-card card-primary">
        <div class="card-body text-center">
            <div class="round-box">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                        <g>
                                        <g>
                                            <path d="M256,0C114.615,0,0,114.615,0,256s114.615,256,256,256s256-114.615,256-256S397.385,0,256,0z M96,100.16                                            c50.315,35.939,80.124,94.008,80,155.84c0.151,61.839-29.664,119.919-80,155.84C11.45,325.148,11.45,186.851,96,100.16z M256,480                                            c-49.143,0.007-96.907-16.252-135.84-46.24C175.636,391.51,208.14,325.732,208,256c0.077-69.709-32.489-135.434-88-177.6                                            c80.1-61.905,191.9-61.905,272,0c-98.174,75.276-116.737,215.885-41.461,314.059c11.944,15.577,25.884,29.517,41.461,41.461                                            C353.003,463.884,305.179,480.088,256,480z M416,412v-0.16c-86.068-61.18-106.244-180.548-45.064-266.616                                            c12.395-17.437,27.627-32.669,45.064-45.064C500.654,186.871,500.654,325.289,416,412z"></path>
                                        </g>
                                        </g>
                                    </svg>
            </div>
            <h5 class="tw-whitespace-nowrap" style="color: #2f7f70;">Rp {{ toIdrCurrency(dataEnergyCost) }}</h5>
            <h6>Total Energy Cost Feb 2023 (Listrik + BBM)</h6>
            <a class="btn-arrow arrow-secondary" style="color: #2f7f70;" href="#">
                <i class="toprightarrow-primary me-2"><VueFeather type="arrow-up-right" strokeWidth="4" style="width:0.8rem" class="ms-1 mt-1" /></i>9.54%
            </a>
            <div class="parrten">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewbox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g><path d="M256,0C114.615,0,0,114.615,0,256s114.615,256,256,256s256-114.615,256-256S397.385,0,256,0z M96,100.16 c50.315,35.939,80.124,94.008,80,155.84c0.151,61.839-29.664,119.919-80,155.84C11.45,325.148,11.45,186.851,96,100.16z M256,480                                            c-49.143,0.007-96.907-16.252-135.84-46.24C175.636,391.51,208.14,325.732,208,256c0.077-69.709-32.489-135.434-88-177.6                                            c80.1-61.905,191.9-61.905,272,0c-98.174,75.276-116.737,215.885-41.461,314.059c11.944,15.577,25.884,29.517,41.461,41.461                                            C353.003,463.884,305.179,480.088,256,480z M416,412v-0.16c-86.068-61.18-106.244-180.548-45.064-266.616                                            c12.395-17.437,27.627-32.669,45.064-45.064C500.654,186.871,500.654,325.289,416,412z"></path></g></g></svg>
            </div>
        </div>
    </div>
</template>