// State
//  data ...
// Mutations
//
//
// Actions
import lead from "./modules/lead.js?31ss3423";
import lead_new from "./modules/lead_new_2.js?12usy3";
// import customer from "../master-customer/modules/customer.js";
import customer_new from "../master-customer/modules/customer_new.js?abcd21243e";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL",
        view: false,
        report_url: ''
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
        lead: lead,
        lead_new: lead_new,
        // customer: customer,
        customer_new: customer_new,
        system: system
    }
});