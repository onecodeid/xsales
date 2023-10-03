// State
//  data ...
// Mutations
//
//
// Actions
import payment from "./modules/payment.js?t=asd";
import payment_new from "./modules/payment_new.js?t=12daas3";
import invoice from "../sales-invoice/modules/invoice.js?t=asd";
import invoice_new from "../sales-invoice/modules/invoice_new.js?t=asd";
import journal from "../trans-journal/modules/journal.js?1234";
import journal_new from "../trans-journal/modules/journal_new.js?1234";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
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
        },

        set_dialog_print (state, v) {
            state.dialog_print = v
        }
    },

    modules : {
        payment: payment,
        payment_new: payment_new,
        invoice: invoice,
        invoice_new: invoice_new,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});
