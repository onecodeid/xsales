// State
//  data ...
// Mutations
//
//
// Actions
import purchase from "./modules/purchase.js?t=3fd2sd541";
import purchase_new from "./modules/purchase_new.js?t=134sddsdf23";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
        dialog_progress: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL"
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_confirm(state, v) {
            state.dialog_confirm = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        }
    },

    modules: {
        purchase: purchase,
        purchase_new: purchase_new,
        system: system
    }
});