
export const mustBeRtuCode = (value) => {
    const regexp = /^[a-zA-Z0-9-]+$/;
    return regexp.test(value);
};