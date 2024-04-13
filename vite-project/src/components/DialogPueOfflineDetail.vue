<script setup>
import { ref, computed } from "vue";
import { isFileImg } from "@/helpers/file";
import { toNumberText } from "@/helpers/number-format";
import Dialog from "primevue/dialog";
import Image from "primevue/image";

const emit = defineEmits(["close"]);
const props = defineProps({
    dataPue: { type: Object, required: true }
});

const showDialog = ref(true);
const data = computed(() => {
    const data = props.dataPue;
    const result = {
        dayaSdpA: data.daya_sdp_a ? toNumberText(data.daya_sdp_a) + " Watt" : "-",
        dayaSdpB: data.daya_sdp_b ? toNumberText(data.daya_sdp_b) + " Watt" : "-",
        dayaSdpC: data.daya_sdp_c ? toNumberText(data.daya_sdp_c) + " Watt" : "-",
        powerFactorSdp: data.power_factor_sdp ? toNumberText(data.power_factor_sdp) : "-",
        dayaEqpA: data.daya_eq_a ? toNumberText(data.daya_eq_a) + " Watt" : "-",
        dayaEqpB: data.daya_eq_b ? toNumberText(data.daya_eq_b) + " Watt" : "-",
        dayaEqpC: data.daya_eq_c ? toNumberText(data.daya_eq_c) + " Watt" : "-",
        pueValue: data.pue_value ? toNumberText(data.pue_value) : "-",
        evidence: data.evidence,
        evidenceUrl: data.evidence_url
    };

    const dayaEsensial = Number(data.daya_sdp_a || 0) + Number(data.daya_sdp_b || 0) + Number(data.daya_sdp_c || 0);
    const ictEquip = Number(data.daya_eq_a || 0) + Number(data.daya_eq_b || 0) + Number(data.daya_eq_c || 0);

    result.dayaEsensial = toNumberText(dayaEsensial) + " Watt";
    result.ictEquip = toNumberText(ictEquip) + " Watt";
    return result;
});

const periode = computed(() => {
    const data = props.dataPue;
    return new Intl
        .DateTimeFormat('id', {
            dateStyle: 'long',
            timeStyle: 'short'
        })
        .format(new Date(data.created_at));
});
</script>
<template>
    <Dialog header="Data PUE Offline" v-model:visible="showDialog" maximizable modal draggable
        class="dialog-basic" @afterHide="$emit('close')">
        <div class="p-4">
            <p class="text-end">Periode {{ periode }}</p>
            <div class="mb-4">
                <table class="table table-bordered">
                    <tr>
                        <td colspan="2"><b>Total Daya Essential Facility</b></td>
                        <td>: <b>{{ data.dayaEsensial }}</b></td>
                    </tr>
                    <tr>
                        <td class="tw-w-[1px]"></td>
                        <td>Daya Total Air Conditioner (AC) Essential</td>
                        <td>: {{ data.dayaSdpA }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Lampu & Exhaust Fan Essential</td>
                        <td>: {{ data.dayaSdpB }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Rectifier & Inverter Essential</td>
                        <td>: {{ data.dayaSdpC }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Power Factor Air Conditioner Essential (Cos Phi)</b></td>
                        <td>: <b>{{ data.powerFactorSdp }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Total Daya ICT Equipment</b></td>
                        <td>: <b>{{ data.ictEquip }}</b></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Beban Output DC Rectifier Source A</td>
                        <td>: {{ data.dayaEqpA }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Beban Output DC Rectifier Source B</td>
                        <td>: {{ data.dayaEqpB }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya ICT Equipment Lain</td>
                        <td>: {{ data.dayaEqpC }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Nilai PUE</b></td>
                        <td>: <b>{{ data.pueValue }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">Evidence</td>
                        <td>
                            <div v-if="isFileImg(data.evidence)" class="position-relative">
                                <span>{{ data.evidence }}</span>
                                <Image :src="data.evidenceUrl" alt="gambar evidence" preview class="img-stretched-link" />
                            </div>
                            <a v-else :href="data.evidenceUrl" target="_blank">{{ data.evidence }}</a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" @click="showDialog = false" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </Dialog>
</template>
<style scoped>

.table-bordered>:not(caption)>*>* {
    @apply tw-border-0 tw-p-0 tw-shadow-none;
}

</style>