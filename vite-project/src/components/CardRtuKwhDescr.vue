<script setup>
import { ref, computed } from "vue";
import { useMonitoringRtuStore } from "@stores/monitoring-rtu";
import { toIdrCurrency, toNumberText } from "@helpers/number-format";
import Skeleton from "primevue/skeleton";

const monitoringRtuStore = useMonitoringRtuStore();
const emit = defineEmits(["update:loading"]);
const props = defineProps({
    loading: { required: true },
});

const isLoading = computed({
    get() {
        return props.loading;
    },
    set(val) {
        emit("update:loading", val);
    }
});

const titleText = computed(() => {
    const rtu = monitoringRtuStore.rtu;
    return rtu ? `${ rtu.rtu_name } Need Atention!` : "";
});

const descrText = computed(() => {
    const savingMonthly = monitoringRtuStore.kwhSaving?.monthly;
    const plnCostMonthly = monitoringRtuStore.plnCostMonthly;

    let savingText = "";
    if(savingMonthly?.kwh_percent > 0)
        savingText = `Tercatat peningkatan penggunaan listrik sebesar ${ savingMonthly.kwh_percent }% dibanding bulan lalu.`;
    else if(savingMonthly?.kwh_percent < 0)
        savingText = `Tercatat penurunan penggunaan listrik sebesar ${ savingMonthly.kwh_percent }% dibanding bulan lalu.`;

    let lwbpText = "";
    let kwhCostText = "";
    let lwbpAlertText = "";
    if(plnCostMonthly.length > 0) {

        const plnCostMonthlyLastIndex = plnCostMonthly.length - 1;
        const kwhCurrMonth = plnCostMonthly[plnCostMonthlyLastIndex];
        const kwhPrevMonth = plnCostMonthly[plnCostMonthlyLastIndex - 1];

        if(
            kwhCurrMonth?.lwbp !== null && kwhCurrMonth?.lwbp !== undefined
            && kwhPrevMonth?.lwbp !== null && kwhPrevMonth?.lwbp !== undefined
        ) {
            if(kwhCurrMonth?.lwbp > kwhPrevMonth?.lwbp) {
                if(!savingText) lwbpText = "Penggunaan LWBP terindikasi mengalami peningkatan.";
                else lwbpText = "Penggunaan LWBP juga terdapat indikasi peningkatan.";
                lwbpAlertText = "Segera cek dan pastikan penggunaan listrik di LWBP.";
            } else if(kwhCurrMonth?.lwbp < kwhPrevMonth?.lwbp) {
                if(!savingText) lwbpText = "Penggunaan LWBP terindikasi mengalami penurunan.";
                else lwbpText = "Penggunaan LWBP juga terdapat indikasi penurunan.";
            }
        }

        if(
            kwhCurrMonth?.kwh_cost !== null && kwhCurrMonth?.kwh_cost !== undefined
            && kwhPrevMonth?.kwh_cost !== null && kwhPrevMonth?.kwh_cost !== undefined
        ) {
            if(kwhCurrMonth?.kwh_cost > kwhPrevMonth?.kwh_cost)
                kwhCostText = "Biaya tagihan meningkat bulan ini dibanding bulan kemarin.";
            else if(kwhCurrMonth?.kwh_cost < kwhPrevMonth?.kwh_cost)
                kwhCostText = "Biaya tagihan di bulan ini turun dibanding bulan kemarin.";
            else
                kwhCostText = "Biaya tagihan masih relatif sama di bulan ini dibanding bulan kemarin.";
        } else if(kwhCurrMonth?.kwh_cost !== null && kwhCurrMonth?.kwh_cost !== undefined) {
            if(!savingText && !lwbpText) {
                kwhCostText = `Penggunaan energi listrik di bulan ini sebesar ${ toNumberText(kwhCurrMonth.kwh_usage) }KwH`
                    + ` dengan estimasi biaya Rp ${ toIdrCurrency(kwhCurrMonth.kwh_cost) }.`;
            }
        }

    }

    let result = [ savingText, lwbpText, kwhCostText, lwbpAlertText ].filter(item => item).join(" ");
    if(result) return result;

    // savingMonthly and plnCostMonthly data is empty
    result = "Data penggunaan energi listrik dalam dua bulan terakhir tidak dapat ditemukan";

    const latestPlnCostMonthYear = plnCostMonthly.reduce((latest, item) => {
        if(item?.kwh_usage)
            latest = `${ item.month_name } ${ item.year }`;
        return latest;
    }, null);
    if(latestPlnCostMonthYear)
        result += `, terakhir pada bulan ${ latestPlnCostMonthYear }`;

    result += ". Harap memeriksa ketersediaan data RTU pada database Densus.";
    return result;
});
</script>
<template>
    <Skeleton v-if="isLoading" width="100%" height="20rem" class="mb-4" />
    <div v-else class="card profile-greeting">
        <div class="card-header">
            <div class="header-top">
                <div class="setting-list bg-primary position-unset">
                    <ul class="list-unstyled setting-option">
                        <li>
                            <div class="setting-white d-flex justify-content-center align-items-center pt-1">
                                <VueFeather type="settings" />
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body text-center p-t-0">
            <h3 class="font-light"> {{ titleText }}</h3>
            <p>{{ descrText }}</p>
        </div>
        <div class="confetti">
            <div v-for="n in 13" class="confetti-piece"></div>
        </div>
    </div>
</template>