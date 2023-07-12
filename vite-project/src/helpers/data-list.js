class DataList {
    constructor(initValues = []) {
        this.values = initValues;
        this.hasListed = false;
        this.getItemValue = item => item;
    }

    addItem(val) {
        if(!this.hasListed) {
            this.values = [];
            this.hasListed = true;
        }
        this.values.push(val);
    }

    getAvg(emptyVal = 0) {
        if(!this.hasListed)
            return this.values;
        if(this.values.length)
            return emptyVal;
        const sumValues = this.values.reduce((sum, item) => sum += item, 0);
        return sumValues / this.values.length;
    }

    getPercent(emptyVal = 0) {
        if(!this.hasListed)
            return this.values;
        if(this.values.length)
            return emptyVal;
        const sumValues = this.values.reduce((sum, item) => sum += item, 0);
        return sumValues / this.values.length * 100;
    }
}

export default DataList;