<script setup>
import { ref, computed } from "vue";
import { isFileImg } from "@helpers/file";
import { toNumberText } from "@helpers/number-format";
import Dialog from "primevue/dialog";
import Image from "primevue/image";

const emit = defineEmits(["close"]);
const props = defineProps({
    dataPue: { type: Object, required: true }
});

const showDialog = ref(true);

const dayaEsensial = computed(() => {
    const data = props.dataPue;
    return Number(data.daya_sdp_a || 0) + Number(data.daya_sdp_b || 0) + Number(data.daya_sdp_c || 0);
});

const ictEquip = computed(() => {
    const data = props.dataPue;
    return Number(data.daya_eq_a || 0) + Number(data.daya_eq_b || 0) + Number(data.daya_eq_c || 0);
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
                        <td>: <b>{{ toNumberText(dayaEsensial) }}</b></td>
                    </tr>
                    <tr>
                        <td class="tw-w-[1px]"></td>
                        <td>Daya Total Air Conditioner (AC) Essential - [Watt]</td>
                        <td>: {{ toNumberText(dataPue.daya_sdp_a) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Lampu & Exhaust Fan Essential - [Watt]</td>
                        <td>: {{ toNumberText(dataPue.daya_sdp_b) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Rectifier & Inverter Essential - [Watt]</td>
                        <td>: {{ toNumberText(dataPue.daya_sdp_c) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Power Factor Air Conditioner Essential - [Cos Phi]</b></td>
                        <td>: <b>{{ dataPue.power_factor_sdp ? toNumberText(dataPue.power_factor_sdp): '-' }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Total Daya ICT Equipment</b></td>
                        <td>: <b>{{ toNumberText(ictEquip) }}</b></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Beban Rectifier Source A - [Watt]</td>
                        <td>: {{ toNumberText(dataPue.daya_eq_a) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya Total Beban Rectifier Source B - [Watt]</td>
                        <td>: {{ toNumberText(dataPue.daya_eq_b) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Daya ICT Equipment Lain - [Watt]</td>
                        <td>: {{ dataPue.daya_eq_c ? toNumberText(dataPue.daya_eq_c) : '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Nilai PUE</b></td>
                        <td>: <b>{{ toNumberText(dataPue.pue_value) }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">Evidence</td>
                        <td>
                            <div v-if="isFileImg(dataPue.evidence)" class="position-relative">
                                <span>{{ dataPue.evidence }}</span>
                                <Image :src="dataPue.evidence_url" alt="gambar evidence" preview class="img-stretched-link" />
                            </div>
                            <a v-else :href="dataPue.evidence_url" target="_blank">{{ dataPue.evidence }}</a>
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