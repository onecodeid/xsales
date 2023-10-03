// State
//  data ...
// Mutations
//
//
// Actions
import budgeting from "./modules/budgeting.js?t=1w2345";
// import account_new from "./modules/account_new.js?t=12ss345ss67";
// import account_trans from "./modules/account_trans.js?t=4567";
// import journal_new from "../trans-journal/modules/journal_new.js?t=123"
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
        budgeting: budgeting,
        // account_new: account_new,
        // accountTrans: account_trans,
        // journal_new: journal_new,
        system: system
    }
});