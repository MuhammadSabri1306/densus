import { ref } from "vue";

export const useCollapseRow = () => {
    const collapsedDivre = ref([]);
    const collapsedWitel = ref([]);
    
    const collapseRow = (type, code) => {
        if(type == "divre")
            collapsedDivre.value = [...collapsedDivre.value, code];
        if(type == "witel")
            collapsedWitel.value = [...collapsedWitel.value, code];
    };
    
    const expandRow = (type, code) => {
        if(type == "divre")
            collapsedDivre.value = collapsedDivre.value.filter(item => item !== code);
        if(type == "witel")
            collapsedWitel.value = collapsedWitel.value.filter(item => item !== code);
    };
    
    const toggleRowCollapse = (type, code) => {
        if(!type || !code)
            return;
        const isExpand = (type == "divre") ? collapsedDivre.value.indexOf(code) < 0 : collapsedWitel.value.indexOf(code) < 0;
        if(isExpand)
            collapseRow(type, code);
        else
            expandRow(type, code);
    };

    const initCollapseDivre = codes => {
        if(Array.isArray(codes))
            codes.forEach(code => collapseRow("divre", code));
    };

    const initCollapseWitel = codes => {
        if(Array.isArray(codes))
            codes.forEach(code => collapseRow("witel", code));
    };

    return { collapsedDivre, collapsedWitel, toggleRowCollapse, initCollapseDivre, initCollapseWitel };
};