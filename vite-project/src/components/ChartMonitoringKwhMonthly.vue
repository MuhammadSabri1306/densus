<script setup>
import { ref, computed } from "vue";
import { useKwhStore } from "@/stores/kwh";
import { useUserStore } from "@stores/user";
import { toNumberText } from "@helpers/number-format";
import VueApexCharts from "vue3-apexcharts";

const kwhStore = useKwhStore();
const userStore = useUserStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = kwhStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const titleYear = computed(() => {
    const filter = kwhStore.filters;
    return `Tahun ${ filter.year }`;
});

const series = ref([]);
const monthCategory = ref([]);
const chartOptions = computed(() => {
    const titleText = titleYear.value;

    return {
        chart: {
            animations: { enabled: true }
        },
        xaxis: {
            categories: monthCategory.value,
            // labels: {
            //     style: { cssClass: "tw-uppercase" }
            // }
        },
        yaxis: {
            labels: {
                formatter: value => {
                    if(value === null)
                        return null;
                    const val = value / 1000000;
                    return `${ toNumberText(val) } juta KwH`;
                }
            }
        },
        title: { text: titleText, align: "left" },
        curve: "smooth",
        dataLabels: { enabled: false }
    };
});

const buildSeriesData = data => {
    const groupType = level.value == "witel" ? "sto"
        : level.value == "divre" ? "witel"
        : "divre";
    const groupedData = {};
    data.forEach(item => {

        const matchKey = groupType + "_kode";
        const groupKey = item[matchKey];
        if(!groupedData[groupKey]) {
            const nameKey = groupType + "_name";
            groupedData[groupKey] = {
                name: item[nameKey],
                kwh: {}
            };
        }

        for(const monthKey in item.kwh_values) {
            if(!groupedData[groupKey].kwh[monthKey])
                groupedData[groupKey].kwh[monthKey] = [];
            if(item.kwh_values[monthKey] !== null) {
                groupedData[groupKey].kwh[monthKey].push(item.kwh_values[monthKey].value);
            }
        }

    });

    const result = Object.values(groupedData)
        .sort((a, b) => {
            const nameA = a.name.toUpperCase();
            const nameB = b.name.toUpperCase();
            return (nameA < nameB) ? -1 : (nameA > nameB) ? 1 : 0;
        })
        .map(({ name, kwh }) => {
            const seriesDataItem = Object.values(kwh)
                .map(kwhValues => {
                    const value = kwhValues.length < 1 ? null : kwhValues.reduce((a, b) => a + b, 0);
                    return value;
                });
            return { name, data: seriesDataItem };
        });
    return result;
};

const setSrcData = data => {
    if(!data.kwh_data || !data.month_column) {
        series.value = [];
        return;
    }

    monthCategory.value = data.month_column.map(monthNumber => {
        console.log(kwhStore.monthList[monthNumber]?.name);
        return kwhStore.monthList[monthNumber]?.name;
    });
    series.value = buildSeriesData(data.kwh_data);
};

defineExpose({ setSrcData });
</script>
<template>
    <div class="card income-card">
        <div class="card-header pb-0">
            <h5>Monitoring Nilai KwH</h5>
        </div>
        <div class="card-body">
            <VueApexCharts width="100%" height="380px" type="line" :options="chartOptions" :series="series" />
        </div>
    </div> 
</template>