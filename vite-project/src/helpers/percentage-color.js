
const lowLimit = {
    warning: 25,
    standard: 50,
    good: 75,
    excellent: 100
};

export const inRange = {
    danger: value => value < lowLimit.warning,
    warning: value => lowLimit.warning <= value && value < lowLimit.standard,
    standard: value => lowLimit.standard <= value && value < lowLimit.good,
    good: value => lowLimit.good <= value && value < lowLimit.excellent,
    excellent: value => lowLimit.excellent <= value
};

export const getPercentageTextClass = value => {
    return {
        "tc-percentage-danger": inRange.danger(value),
        "tc-percentage-warning": inRange.warning(value),
        "tc-percentage-standard": inRange.standard(value),
        "tc-percentage-good": inRange.good(value),
        "tc-percentage-excellent": inRange.excellent(value)
    };
};

export const getPercentageBgClass = value => {
    return {
        "bgc-percentage-danger": inRange.danger(value),
        "bgc-percentage-warning": inRange.warning(value),
        "bgc-percentage-standard": inRange.standard(value),
        "bgc-percentage-good": inRange.good(value),
        "bgc-percentage-excellent": inRange.excellent(value)
    };
};