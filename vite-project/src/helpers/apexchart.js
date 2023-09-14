export const basicPalettes = {
    palette1: ["#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0"],
    palette2: ["#3F51B5", "#03A9F4", "#4CAF50", "#F9CE1D", "#FF9800"],
    palette3: ["#33B2DF", "#546E7A", "#D4526E", "#13D8AA", "#A5978B"],
    palette4: ["#4ECDC4", "#C7F464", "#81D4FA", "#546E7A", "#FD6A6A"],
    palette5: ["#2B908F", "#F9A3A4", "#90EE7E", "#FA4443", "#69D2E7"],
    palette6: ["#449DD1", "#F86624", "#EA3546", "#662E9B", "#C5D86D"],
    palette7: ["#D7263D", "#1B998B", "#2E294E", "#F46036", "#E2C044"],
    palette8: ["#662E9B", "#F86624", "#F9C80E", "#EA3546", "#43BCCD"],
    palette9: ["#5C4742", "#A5978B", "#8D5B4C", "#5A2A27", "#C4BBAF"],
    palette10: ["#A300D6", "#7D02EB", "#5653FE", "#2983FF", "#00B1F2"],
};

export const getAllPalettes = () => {
    return Object.values(basicPalettes).reduce((palettes, item) => [...palettes, ...item], []);
};

export const generateUniqueStyle = (length, palettes, dashStep = 2) => {
    if(typeof palettes == "string" && palettes in basicPalettes)
        palettes = basicPalettes[palettes];
    if(typeof palettes == "string" && palettes == "all")
        palettes = getAllPalettes();

    const colors = [];
    const dashes = [];

    let dashSize = 0;
    let palettesIndex = 0;
    
    for(let i=0; i<length; i++) {
        colors.push(palettes[palettesIndex]);
        dashes.push(dashSize);
        palettesIndex++;

        if(palettesIndex == palettes.length) {
            dashSize += dashStep;
            palettesIndex = 0;
        }
    }

    return { colors, dashes };
};