// State
//  data ...
// Mutations
//
//
// Actions
import payment from "./modules/payment.js";
import payment_new from "./modules/payment_new.js";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_confirm: false
    },

    mutations : {
        set_dialog_delete (state, v) {
            state.dialog_delete = v
        },

        set_dialog_confirm (state, v) {
            state.dialog_confirm = v
        }
    },

    modules : {
        payment: payment,
        payment_new: payment_new,
        system: system
    }
});
