import { ref, inject, watch, onMounted } from "vue";
import ComponentDefault from "./Component.vue";
import ComponentManualResolve from "./ComponentManual.vue";
import ComponentAsyncSetup from "./ComponentAsyncSetup.vue";

export const AsyncWrapper = ComponentDefault;

export const manualResolveInjectionKey = Symbol();
export const AsyncManualWrapper = ComponentManualResolve;
export const useAsyncManualWrapper = () => {

    const { childState, setChildState } = inject(manualResolveInjectionKey);
    const currState = ref(childState.value);
    watch(currState, val => {
        if(val != childState.value)
            setChildState(val);
    });

    const loadingComponent = () => currState.value = "loading";
    const resolveComponent = () => currState.value = "resolve";
    const rejectComponent = () => currState.value = "error";

    return { loadingComponent, resolveComponent, rejectComponent };
};

export const asyncSetupInjectionKey = Symbol();
export const AsyncSetupWrapper = ComponentAsyncSetup;
export const useAsyncSetupWrapper = () => {

    const { childState, setChildState } = inject(asyncSetupInjectionKey);
    const currState = ref(childState.value);
    onMounted(() => {
        if(currState.value != childState.value)
            setChildState(currState.value);
    });

    const resolveComponent = () => currState.value = "resolve";
    const rejectComponent = () => currState.value = "error";

    return { resolveComponent, rejectComponent };
};