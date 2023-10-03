// State
//  data ...
// Mutations
//
//
// Actions
import accountreport from "./modules/accountreport.js?t=1w2345";
import journal_new from "../trans-journal/modules/journal_new.js?t=123"
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
        accountreport: accountreport,
        journal_new: journal_new,
        system: system
    }
});