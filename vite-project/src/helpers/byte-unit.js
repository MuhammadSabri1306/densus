class BytesUnit {
    constructor(num, exponent = 0) {
        this.bytes = (exponent < 1) ? num : num * (1024 ** exponent);
        this.multiplier = 1024;
    }
    toBytes() {
        return this.bytes;
    }
    toKb() {
        return this.bytes / this.multiplier;
    }
    toMb() {
        return this.toKb() / this.multiplier;
    }
    toGb() {
        return this.toMb() / this.multiplier;
    }
    toTb() {
        return this.toGb() / this.multiplier;
    }
    toAutoText() {
        if(this.bytes === 0) return
            "0 B";
            
        const unitList = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
        const i = Math.floor(Math.log(this.bytes) / Math.log(this.multiplier));
        return parseFloat((this.bytes / Math.pow(this.multiplier, i)).toFixed(2)) + " " + unitList[i];
    }
};

export const fromBytes = num => new BytesUnit(num);
export const fromKb = num => new BytesUnit(num, 1);
export const fromMb = num => new BytesUnit(num, 2);
export const fromGb = num => new BytesUnit(num, 3);
export const fromTb = num => new BytesUnit(num, 4);