
const imgExtensions = ["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"];
const pdfExtensions = ["pdf"];
const excelExtensions = ["xls", "xlsx"];
const wordExtensions = ["doc", "docx"];
const pptExtensions = ["ppt", "pptx"];

export const getFileExt = filename => {
    const filenameArr = filename.split(".");
    return filenameArr[filenameArr.length - 1];
};

export const getFileName = filepath => {
    const filepathArr = filepath.split("/");
    return filepathArr[filepathArr.length - 1];
};

export const getFileRawName = filename => {
    const filenameArr = filename.split(".");
    return filenameArr.slice(0, filenameArr.length - 1);
};

export const getFileTypeByExt = ext => {
    const extensions = [
        ...imgExtensions.map(item => ({ ext: item, type: "image" })),
        ...pdfExtensions.map(item => ({ ext: item, type: "pdf" })),
        ...excelExtensions.map(item => ({ ext: item, type: "excel" })),
        ...wordExtensions.map(item => ({ ext: item, type: "word" })),
        ...pptExtensions.map(item => ({ ext: item, type: "presentation" }))
    ];
    
    const extIndex = extensions.findIndex(item => item.ext == ext);
    if(extIndex < 0)
        return null;
    return extensions[extIndex].type;
};

export const getFileType = filename => {
    const ext = getFileExt(filename);
    return getFileTypeByExt(ext);
};

export const isExtImg = ext => imgExtensions.indexOf(ext) >= 0;
export const isFileImg = filename => isExtImg(getFileExt(filename));

export const isExtPdf = ext => pdfExtensions.indexOf(ext) >= 0;
export const isFilePdf = filename => isExtPdf(getFileExt(filename));

export const isExtExcel = ext => excelExtensions.indexOf(ext) >= 0;
export const isFileExcel = filename => isExtExcel(getFileExt(filename));

export const isExtWord = ext => wordExtensions.indexOf(ext) >= 0;
export const isFileWord = filename => isExtWord(getFileExt(filename));

export const isExtPpt = ext => pptExtensions.indexOf(ext) >= 0;
export const isFilePpt = filename => isExtPpt(getFileExt(filename));