
export const toIdrCurrency = numb => {
	let numberStr = parseFloat(numb);
    numberStr = numberStr.toString();
	const split = numberStr.split(".");
	const left = split[0].length % 3;
	let result = split[0].substr(0, left);
	const digit = split[0].substr(left).match(/\d{3}/gi);

	if(digit){
		const separator = left ? "." : "";
		result += separator + digit.join(".");
	}

	result = split[1] != undefined ? result + "," + split[1] : result;
	return result;
};

export const toFixedNumber = (numb, maxDecimal = 2, normalizeZero = false) => {
	if(typeof numb != "number")
		numb = Number(numb);
	const isNegative = numb < 0;
	if(isNegative)
		numb = Math.abs(numb);

	const numbStrArr = numb.toString().split(".");
	const afterDecimalNumb = numbStrArr.length > 1 ? numbStrArr[1] : "";

	if(afterDecimalNumb.length <= maxDecimal)
		return isNegative ? -numb : numb;

	const minDecimalIndex = afterDecimalNumb.match(/[1-9]/)?.index ?? -1;
	const minDecimal = minDecimalIndex + 1;

	const decimalCount = Math.max(minDecimal, maxDecimal);
	numb = Math.round(numb * Math.pow(10, decimalCount)) / Math.pow(10, decimalCount);
	return isNegative ? -numb : numb;
};

export const toNumberText = (numb, maxDecimal = 2) => {
	if(typeof numb != "number")
		numb = Number(numb);
	const isNegative = numb < 0;
	if(isNegative)
		numb = Math.abs(numb);

	const numbStr = toFixedNumber(numb, maxDecimal).toString();
	let [ baseNumb, decimalNumb ] = numbStr.split(".");

	if(baseNumb.length > 3) {
        let dotIndex = baseNumb.length % 3;
		dotIndex = dotIndex === 0 ? 3 : dotIndex;
		for(let i=dotIndex; i<baseNumb.length; i+=4) {
            baseNumb = baseNumb.slice(0, i) + "." + baseNumb.slice(i);
        }
    }

	let result = baseNumb;
	if(decimalNumb !== undefined)
		result += `,${ decimalNumb }`;
	if(isNegative)
		result = `-${ result }`;
	return result;
};

export const toZeroLeading = (numb, maxDigit) => {
	numb = Number(numb).toString();
	
	if(numb.length >= maxDigit)
		return numb;
	
	for(let i=0; i<(maxDigit-numb.length); i++) {
		numb = "0" + numb;
	}
	return numb;
};

export const toRoman = num => {
	num = Number(num);
	const romanNumerals = [
		{ value: 1000, symbol: "M" },
		{ value: 900, symbol: "CM" },
		{ value: 500, symbol: "D" },
		{ value: 400, symbol: "CD" },
		{ value: 100, symbol: "C" },
		{ value: 90, symbol: "XC" },
		{ value: 50, symbol: "L" },
		{ value: 40, symbol: "XL" },
		{ value: 10, symbol: "X" },
		{ value: 9, symbol: "IX" },
		{ value: 5, symbol: "V" },
		{ value: 4, symbol: "IV" },
		{ value: 1, symbol: "I" }
	];
	  
	let result = "";
	for(let i=0; i<romanNumerals.length; i++) {
		while (num >= romanNumerals[i].value) {
			result += romanNumerals[i].symbol;
			num -= romanNumerals[i].value;
		}
	}
	return result;
};

export const toNewosasePortValue = (value, unit, identifier) => {
	let portValue = `${ toNumberText(value) } ${ unit }`;

	if(identifier.toLowerCase() == "st_pln") {
		if(value === 1) portValue = "OFF";
		else if(value === 0) portValue = "ON";
		return portValue;
	}

	if(identifier.toLowerCase() == "st_deg") {
		if(value === 1) portValue = "ON";
		else if(value === 0) portValue = "OFF";
		return portValue;
	}

	if(unit.toLowerCase() == "on/off") {
		if(value === 1) portValue = "ON";
		else if(value === 0) portValue = "OFF";
		return portValue;
	}

	return portValue;
};