const valueMarker = "{value}";

export const basicConfig = {
  maxResults: 6,
  highlight: false,
  showScore: false,
  marker: `'${ valueMarker }'`
};

const setupConfig = config => {
	const maxItems = config.maxItems || basicConfig.maxResults;
	const highlight = config.highlight || basicConfig.highlight;
	const marker = config.marker ||basicConfig.marker;
	const showScore = config.showScore ? config.showScore : false;

	return { maxItems, highlight, marker, showScore };
};

const createRegeExp = keyword => {
	const keyRegx = [];
	for(let i=0; i<keyword.length; i++){
		const keyRegxItem = (i < keyword.length - 1) ? keyword[i] + keyword.slice(1).split("").map(q => q + "*").join("") : keyword[i];
		keyRegx.push(keyRegxItem);
	}

	const regx = keyRegx.map(item => "(" + item + ")").join("|");
	return new RegExp(regx, "g");
};

export const search = (keyword, model, config = {}) => {
	let { maxItems, highlight, marker, showScore } = setupConfig(config);

	// first char filter
	model = model.filter(item => {
		// item's first char must be same with keyword's first char
		if(item[0] !== keyword[0])
			return false;

		// item's must contain all char in keyword
		for(let kChar of keyword.split("")) {
			if(item.indexOf(kChar) < 0)
				return false;
		}

		return true;
	});

	//scoring
	model = model.map(item => {
		let text = item;
		const matchKeys = item.match(createRegeExp(keyword));

		const score = !matchKeys ? 0 : matchKeys.reduce((sum, mItem) => sum + Math.pow(mItem.length, mItem.length), 0);
		if(!highlight)
			return { value: item, score };

		// highlighting
		matchKeys
			.map(mItem => {
				const key = mItem;
				const replacer = mItem.replace(mItem, marker.replace(valueMarker, mItem));
				return { key, replacer };
			})
			.forEach(mItem => text = text.replace(mItem.key, mItem.replacer));

		return { value: item, text, score };
	});

	// order value based on smaller score and limit as maxItems
	model = model.sort((a, b) => b.score - a.score).slice(0, maxItems);
	
	if(showScore)
		return model;
	
	return model.map(item => {
		const { value, text } = item;
		return highlight ? { value, text } : value;
	});
};