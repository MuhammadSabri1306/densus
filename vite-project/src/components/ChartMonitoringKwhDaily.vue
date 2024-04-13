<script setup>
import { ref, reactive, computed } from "vue";
import { useKwhStore } from "@/stores/kwh";
import { useUserStore } from "@/stores/user";
import { toNumberText } from "@/helpers/number-format";
import { generateUniqueStyle } from "@/helpers/apexchart";
import VueApexCharts from "vue3-apexcharts";

const kwhStore = useKwhStore();
const userStore = useUserStore();

const level = computed(() => {
    const userLevel = userStore.level;
    const filters = kwhStore.filters;
    return filters.witel ? "witel" : filters.divre ? "divre" : userLevel;
});

const titleMonthYear = computed(() => {
    const filter = kwhStore.filters;
    const selectedMonth = kwhStore.monthList[filter.month - 1];
    return `${ selectedMonth?.name } ${ filter.year }`;
});

const series = ref([]);
const seriesStyle = reactive({
    colors: [],
    dashes: []
});

const chartOptions = computed(() => {
    const titleText = titleMonthYear.value;

    return {
        chart: {
            animations: { enabled: true }
        },
        stroke: {
            dashArray: seriesStyle.dashes
        },
        xaxis: {
            type: "datetime",
            labels: {
                datetimeUTC: false,
                format: "dd"
            }
        },
        yaxis: {
            labels: {
                formatter: value => value === null ? "-" : `${ toNumberText(value) } KwH`
            }
        },
        title: { text: titleText, align: "left" },
        curve: "smooth"
    };
});

const buildTimestamp = day => {
    const year = kwhStore.filters.year;
    const month = kwhStore.filters.month;
    const date = new Date(`${ year }-${ month }-${ day }`);
    const dateInWIB = date.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });

    const utcDate = new Date(dateInWIB);
    return utcDate.getTime();
};

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

        for(const dayKey in item.kwh_values) {
            if(!groupedData[groupKey].kwh[dayKey])
                groupedData[groupKey].kwh[dayKey] = [];
            if(item.kwh_values[dayKey] !== null) {
                groupedData[groupKey].kwh[dayKey].push(item.kwh_values[dayKey].value);
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
            const seriesDataItem = Object.entries(kwh)
                .map(([dayKey, kwhValues]) => {
                    const timestamp = buildTimestamp(dayKey);
                    const value = kwhValues.length < 1 ? null : kwhValues.reduce((a, b) => a + b, 0);
                    return [ timestamp, value ];
                })
                .sort((itemA, itemB) => (itemA[0] < itemB[0]) ? -1 : (itemA[0] > itemB[0]) ? 1 : 0);
            return { name, data: seriesDataItem };
        });

    const resultFiltered = result.filter(({ data }) => {
        for(let i=0; i<data.length; i++) {
            if(data[i][1]) {
                i = data.length;
                return true;
            }
        }
        return false;
    });

    return resultFiltered.length > 0 ? resultFiltered : result;
};

const setSrcData = data => {
    if(!data.kwh_data) {
        series.value = [];
        return;
    }

    series.value = buildSeriesData(data.kwh_data);
    const { dashes } = generateUniqueStyle(series.value.length, "all");
    seriesStyle.dashes = dashes;
    
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