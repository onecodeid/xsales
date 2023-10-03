// State
//  data ...
// Mutations
//
//
// Actions
import duplicateNew from "./modules/duplicate_new.js?t=1d3";
import sales_new from "../sales-order/modules/sales_new.js?t=1d3";
import unit from "../master-unit/modules/unit.js";
import unit_new from "../master-unit/modules/unit_new.js";
import pack from "../master-pack/modules/pack.js";
import pack_new from "../master-pack/modules/pack_new.js";

import customer_new from "../master-customer/modules/customer_new.js?abfscd21243e";
import customer_address from "../master-customer/modules/customer_address.js?abfscd21243e";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
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
        }
    },

    modules: {
        duplicateNew: duplicateNew,
        sales_new: sales_new,
        unit: unit,
        unit_new: unit_new,
        pack: pack,
        pack_new: pack_new,

        customer_new: customer_new,
        address: customer_address,
        system: system
    }
});