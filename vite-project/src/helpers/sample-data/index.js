export default async (samplePath, defaultData) => {
    try {

        const data = await import(/* @vite-ignore */`./${ samplePath }.js`);
        return data.default;

    } catch(err) {

        console.error(err);
        return defaultData;

    }
};