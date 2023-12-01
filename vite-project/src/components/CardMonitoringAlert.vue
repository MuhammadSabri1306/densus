<script setup>
import { ref } from "vue";

const isResolve = ref(false);
const rtuLocation = ref(null);
const dataSavingMonthly = ref({});

defineExpose({
    resolve: (dataSaving, rtuLoc) => {
        dataSavingMonthly.value = dataSaving.savingmonthly_percent || 0;
        rtuLocation.value = rtuLoc;
        isResolve.value = true;
    }
});
</script>
<template>
    <div v-if="isResolve" class="card profile-greeting">
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
            <div v-for="n in 13" class="confetti-piece"></div>
        </div>
    </div>
    <div v-show="!isResolve">
        <slot name="loading"></slot>
    </div>
</template>