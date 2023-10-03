// State
//  data ...
// Mutations
//
//
// Actions
import journal from "../trans-journal/modules/journal.js";
import journal_new from "../trans-journal/modules/journal_new.js";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_confirm: false,
        filter: ["J.03"],
        title: "JURNAL PENJUALAN"
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
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});
