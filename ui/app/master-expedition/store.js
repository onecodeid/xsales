// State
//  data ...
// Mutations
//
//
// Actions
import expedition from "./modules/expedition.js";
import expedition_new from "./modules/expedition_new.js?t=asssd";
import system from "../assets/js/system.js?d=787";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        report_url: ''
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        expedition: expedition,
        expedition_new: expedition_new,
        system: system
    }
});