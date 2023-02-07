import { defineStore } from "pinia";

export const useUserStore = defineStore("user", {
    state: () => ({
        name: "Muhammad Sabri",
        email: "muhammadsabri1306@gmail.com",
        location: "Makassar",
        token: null
    }),
    getters: {
        hasToken: state => state.token && state.token.length > 0
    }
});