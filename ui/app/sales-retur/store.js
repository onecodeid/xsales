// State
//  data ...
// Mutations
//
//
// Actions
// import receive from "./modules/receive.js";
import retur from "./modules/retur.js?t=1243";
import invoice from "../sales-invoice/modules/invoice.js?t=asd"
import memo from "../finance-memo/modules/memo.js?t=123"
import memoNew from "../finance-memo/modules/memo_new.js?t=123"
// import tag from "../common/modules/tag.js";
// import disc from "../common/modules/disc.js";
import system from "../assets/js/system.js?d=787";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        report_url: '',
        view: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        // receive: receive,
        retur: retur,
        invoice: invoice,
        memo: memo,
        memoNew: memoNew,
        // tag: tag,
        // disc: disc,
        system: system
    }
});