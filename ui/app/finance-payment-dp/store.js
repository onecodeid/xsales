// State
//  data ...
// Mutations
//
//
// Actions
import dp from "./modules/dp.js?t=asdsa";
import dp_new from "./modules/dp_new.js?t=aass1d";
import journal from "../trans-journal/modules/journal.js?12da34";
import journal_new from "../trans-journal/modules/journal_new.js?1234";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_confirm: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL"
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
        dp: dp,
        dp_new: dp_new,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});
