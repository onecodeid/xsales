// State
//  data ...
// Mutations
//
//
// Actions
import receive from "./modules/receive.js?t=123s";
import receive_new from "./modules/receive_new.js?t=12s3";
import purchase from "../purchase-order/modules/purchase.js"
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
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
        }
    },

    modules: {
        receive: receive,
        receive_new: receive_new,
        purchase: purchase,
        system: system
    }
});