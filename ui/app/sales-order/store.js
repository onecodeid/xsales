// State
//  data ...
// Mutations
//
//
// Actions
import sales from "./modules/sales.js?t=1sdssx";
import sales_new from "./modules/sales_new.js?t=saax3xx";
import invoice_new from "../sales-invoice/modules/invoice_new.js?t=1ass1d2423d3";
import invoice from "../sales-invoice/modules/invoice.js?t=12sas1ds23d3";
import item_logpurchase from "../master-item/modules/item_logpurchase.js?t=1278ses3";

import exp from "../master-expedition/modules/expedition.js";
import exp_new from "../master-expedition/modules/expedition_new.js";
// import customer from "../master-customer/modules/customer.js";
import customer_new from "../master-customer/modules/customer_new.js?akjbc234de";
import system from "../assets/js/system.js?t=assssd";

import address from "../master-customer/modules/customer_address.js?t=2sc";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
        dialog_progress: false,
        report_url: '',
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
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_dialog_progress(state, v) {
            state.dialog_progress = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        sales: sales,
        sales_new: sales_new,
        invoice_new: invoice_new,
        invoice: invoice,
        expedition: exp,
        expedition_new: exp_new,
        item_logpurchase: item_logpurchase,
        // customer: customer,
        customer_new: customer_new,
        address: address,
        system: system
    }
});