import { reactive, ref } from "vue";
import { useVuelidate } from "@vuelidate/core";
import { helpers } from "@vuelidate/validators";

export const useDataForm = fields => {
	const state = {},
		validations = {};

	for(let key in fields) {
		const { value, ...validationItem } = fields[key];
		state[key] = (value !== undefined) ? value : null;

		for(let vKey in validationItem) {
			if(validationItem[vKey].message && validationItem[vKey].validator) {
				const { message, validator } = validationItem[vKey];
				validationItem[vKey] = helpers.withMessage(message, validator);
			}
		}

		validations[key] = validationItem;
	}

	const data = reactive(state);
	const v$ = useVuelidate(validations, data);
	const hasSubmitted = ref(false);
    const getInvalidClass = (key, invalidClass) => {
        if(hasSubmitted.value && v$.value[key].$invalid)
            return invalidClass || "invalid";
        return null;
    };

	const getFirstError = (fieldKey = null) => {
		const v = fieldKey ? v$.value[fieldKey] : v$.value;
		if(v.$errors && v.$errors.length > 0)
			return v.$errors[0];
	};

	const getFirstErrorMsg = (fieldKey = null) => {
		const err = getFirstError(fieldKey);
		return err?.$message;
	};

	return { data, v$, hasSubmitted, getInvalidClass, getFirstError, getFirstErrorMsg };
};

export const buildFormData = (data, keysArr) => {
	const formData = new FormData();
	for(let key of keysArr) {
		formData.append(key, data[key]);
	}
	return formData;
};

export const extractSrcValue = (srcData, ...keys) => {
	let value = srcData;

	for(let key of keys) {
		if(value[key] !== undefined)
			value = value[key];
		else
			return;
	}

	return value;
};