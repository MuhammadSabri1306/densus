
export const toIdrCurrency = money => {
    money = money.toString();
    let digit = 0;
    let result = "";

    for(let i=money.length-1; i>=0; i--) {
        digit++;
        if(digit > 3) {
            result = "." + result;
            digit = 0;
        }
        result = money[i] + result;
    }
    return result;
};