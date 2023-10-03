// State
//  data ...
// Mutations
//
//
// Actions
import bill from "./modules/bill.js?t=1s2ss3";
import bill_new from "./modules/bill_new.js";
import journal from "../trans-journal/modules/journal.js?1234";
import journal_new from "../trans-journal/modules/journal_new.js?1234";
import billPay from "./modules/bill_pay.js?t=12dsssssss76s3"
import disc from "../common/modules/disc.js?t=123"
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
        bill: bill,
        bill_new: bill_new,
        billPay: billPay,
        disc: disc,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});