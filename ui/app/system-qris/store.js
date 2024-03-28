// State
//  data ...
// Mutations
//
//
// Actions
// import item_new from "./modules/item_new.js";
import system from "../assets/js/system.js";
import qris from "./modules/qris.js"
export const store = new Vuex.Store({
    state : {
        dialog_delete: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_dialog_confirm(state, v) {
            state.dialog_confirm = v
        }
    },

    modules : {
        // item_new: item_new,
         system: system,
         qris: qris
    }
});
