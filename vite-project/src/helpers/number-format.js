
export const toIdrCurrency = numb => {
	let numberStr = parseFloat(numb);
    numberStr = numberStr.toString();
	const split = numberStr.split('.');
	const left = split[0].length % 3;
	let result = split[0].substr(0, left);
	const digit = split[0].substr(left).match(/\d{3}/gi);

	if(digit){
		const separator = left ? '.' : '';
		result += separator + digit.join('.');
	}

	result = split[1] != undefined ? result + ',' + split[1] : result;
	return result;
};

export const toFixedNumber = (numb, numbBehindComma) => {
	numb = Number(numb);
	return (numb.toString().length > numbBehindComma + 1) ? numb.toFixed(numbBehindComma) : numb.toString();
};