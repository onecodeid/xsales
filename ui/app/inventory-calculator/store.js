// State
//  data ...
// Mutations
//
//
// Actions
import calc from "./modules/calc.js?t=assd";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        }
    },

    modules: {
        calc: calc,
        system: system
    }
});