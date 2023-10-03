// State
//  data ...
// Mutations
//
//
// Actions
import cash from "./modules/cash.js?12345";
import cash_new from "./modules/cash_new.js?12345";
import cash_invoice from "./modules/cash_invoice.js?12345";
import cash_receive from "./modules/cash_receive.js?12345";
import cash_bill from "./modules/cash_bill.js?12345";
// import journal from "../trans-journal/modules/journal.js";
import journal_detail from "./modules/journal.js?12345";
import journal from "../trans-journal/modules/journal.js?12345";
import journal_new from "../trans-journal/modules/journal_new.js?1235";
import system from "../assets/js/system.js?12345";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        filter: ["J.11"],
        tabs: [1, 2],
        selected_tab: 1,
        custom_title: true
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_selected_tab(state, v) {
            state.selected_tab = v
        }
    },

    modules: {
        cash: cash,
        cash_new: cash_new,
        cash_invoice: cash_invoice,
        cash_receive: cash_receive,
        cash_bill: cash_bill,
        journal_detail: journal_detail,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});