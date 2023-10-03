// State
//  data ...
// Mutations
//
//
// Actions
import general from "./modules/general.js?t=123";
import generalNew from "./modules/general_new.js";
import tag from "../common/modules/tag.js";
import system from "../assets/js/system.js?d=787";
import journal from "../trans-journal/modules/journal.js?12345";
import journal_new from "../trans-journal/modules/journal_new.js?1235";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        dialog_confirm: false,
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

        set_dialog_confirm(state, v) {
            state.dialog_confirm = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        general: general,
        generalNew: generalNew,
        tag: tag,
        journal: journal,
        journal_new: journal_new,
        system: system
    }
});