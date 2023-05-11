
const limit = {
    optimize: { high: 1.6 },
    efficient: { middle: 2 },
    average: { low: 2.4 },
    inefficient: { low: 3 }
};

export const inPueRange = {
    optimize: value => limit.optimize.high > value,
    efficient: value => limit.optimize.high <= value && value < limit.average.low,
    average: value => limit.average.low <= value && value < limit.inefficient.low,
    inefficient: value => value >= limit.inefficient.low
};

export const getPueTextClass = pueValue => {
    return {
        "tc-pue-optimize": inPueRange.optimize(pueValue),
        "tc-pue-efficient": inPueRange.efficient(pueValue),
        "tc-pue-average": inPueRange.average(pueValue),
        "tc-pue-inefficient": inPueRange.inefficient(pueValue),
    };
};

export const getPueBgClass = pueValue => {
    console.log(pueValue)
    return {
        "bgc-pue-optimize": inPueRange.optimize(pueValue),
        "bgc-pue-efficient": inPueRange.efficient(pueValue),
        "bgc-pue-average": inPueRange.average(pueValue),
        "bgc-pue-inefficient": inPueRange.inefficient(pueValue)
    };
};