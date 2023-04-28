import { computed } from "vue";

// const pueLimit = [ 1.6, 2, 2.4, 3 ];
const pueLimit = [ 1.6, 2.4, 3 ];

export const getPueTextClass = pueValue => {
    return {
        "tc-optimize": pueValue <= pueLimit[0],
        "tc-efficient": pueValue > pueLimit[0] && pueValue < pueLimit[1],
        "tc-average": pueValue >= pueLimit[1] && pueValue < pueLimit[2],
        "tc-inefficient": pueValue >= pueLimit[2]
    };
};

export const getPueBgClass = pueValue => {
    return {
        "bgc-optimize": pueValue <= pueLimit[0],
        "bgc-efficient": pueValue > pueLimit[0] && pueValue < pueLimit[1],
        "bgc-average": pueValue >= pueLimit[1] && pueValue < pueLimit[2],
        "bgc-inefficient": pueValue >= pueLimit[2]
    };
};