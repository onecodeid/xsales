// State
//  data ...
// Mutations
//
//
// Actions
// import receive from "./modules/receive.js";
import memo from "./modules/memo.js?t=23r4";
import memoNew from "./modules/memo_new.js?t=234";
import memoRefund from "./modules/memo_refund.js?t=2s34";
import tag from "../common/modules/tag.js";
import disc from "../common/modules/disc.js";
import system from "../assets/js/system.js?d=787";
import journal from "../trans-journal/modules/journal.js?12345";
import journal_new from "../trans-journal/modules/journal_new.js?1235";

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
        memo: memo,
        memoNew: memoNew,
        memoRefund: memoRefund,
        tag: tag,
        disc: disc,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});