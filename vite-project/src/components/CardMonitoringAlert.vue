<script setup>
import { computed } from "vue";
import http from "@helpers/http-common";

const props = defineProps({
    rtuCode: { required: true },
    rtuLocation: String
});

let dataSavingMonthly = 0;
try {
    const response = await http.get("/monitoring/savingpercent/" + props.rtuCode);
    if(response.data.savingpercent)
        dataSavingMonthly = response.data.savingpercent.savingmonthly_percent;
} catch (err) {
    console.error(err);
}
</script>
<template>
    <div class="card profile-greeting">
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
            <h3 class="font-light"> {{ rtuLocation }} Need Atention!</h3>
            <p>Tercatat Peningkatan Penggunaan Listrik Sebesar {{ dataSavingMonthly }}% MtD dibanding bulan lalu, Penggunaan LWBP juga terdapat indikasi peningkatan. Biaya Tagihan masih relatif sama di akhir bulan dibanding bulan kemarin. Segera cek dan pastikan penggunaan Listrik di LWBP.</p>
        </div>
        <div class="confetti">
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
            <div class="confetti-piece"></div>
        </div>
    </div>
</template>