import fs from"fs";
import { copyFile } from "cp-file";
import path from"path";

const list = [
    {
        from: "./dist/index.html",
        to: "../application/views/app.php"
    }
];

const getFiles = (dir, files_) => {
    files_ = files_ || [];
    const files = fs.readdirSync(dir);

    files.forEach(filename => {
        const filepath = dir + "/" + filename;
        if(fs.statSync(filepath).isDirectory())
            getFiles(filepath, files_);
        else
            files_.push(filepath);
    });
    
    return files_;
}

const srcAssetsDir = "./dist/assets";
const destAssetsDir = "../assets";
getFiles(srcAssetsDir).forEach(filepath => {
    const from = filepath;
    const to = filepath.replace(srcAssetsDir, destAssetsDir);
    list.push({ from, to });
});

fs.rmSync(destAssetsDir, { recursive: true, force: true });

list.forEach(async (item) => {
    try {
        await copyFile(item.from, item.to);
        console.log(`Copy ${ item.from } to ${ item.to }`);
    } catch(err) {
        console.error(`Error copy ${ item.from } to ${ item.to }`);
    }
});