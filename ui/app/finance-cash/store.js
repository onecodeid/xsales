// State
//  data ...
// Mutations
//
//
// Actions
// import receive from "./modules/receive.js";
import cash from "./modules/cash.js?t=1ssdsssxx";
import cashNew from "./modules/cash_new.js?t=1dddsasddxx";
import tag from "../common/modules/tag.js";
import disc from "../common/modules/disc.js";
import system from "../assets/js/system.js?d=787";
import journal from "../trans-journal/modules/journal.js?12345";
import journal_new from "../trans-journal/modules/journal_new.js?1235";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        dialog_progress: false,
        dialog_calculator: false,
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

        set_dialog_progress(state, v) {
            state.dialog_progress = v
        },

        set_dialog_calculator(state, v) {
            state.dialog_calculator = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        // receive: receive,
        cash: cash,
        cashNew: cashNew,
        tag: tag,
        disc: disc,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});