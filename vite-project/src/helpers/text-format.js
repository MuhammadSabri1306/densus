
export const limitWords = (text, maxWords = 10) => {
    text = text.toString();
    const textArr = text.split(" ");
    if(textArr.length <= maxWords)
        return text;
    return textArr.slice(0, maxWords).join(" ") + "...";
};