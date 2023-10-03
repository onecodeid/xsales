// State
//  data ...
// Mutations
//
//
// Actions
// import receive from "./modules/receive.js";
import invoice from "../sales-invoice/modules/invoice.js?t=1323"
import payable from "./modules/payable.js?t=assad"
import payablePay from "./modules/payable_pay.js?t=12dsssassssss3"
import system from "../assets/js/system.js?d=787"
import disc from "../common/modules/disc.js?t=123"

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
        payable: payable,
        payablePay: payablePay,
        invoice: invoice,
        disc: disc,
        system: system
    }
});