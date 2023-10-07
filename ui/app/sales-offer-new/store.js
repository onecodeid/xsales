// State
//  data ...
// Mutations
//
//
// Actions
import offer from "./modules/offer.js?t=13uj4dse3";
import offer_new from "./modules/offer_new.js?t=1d2as33";
import sales from "../sales-order/modules/sales.js?t=13usdje3";
import sales_new from "../sales-order/modules/sales_new.js?t=1dds3";
import unit from "../master-unit/modules/unit.js";
import unit_new from "../master-unit/modules/unit_new.js";
import pack from "../master-pack/modules/pack.js";
import pack_new from "../master-pack/modules/pack_new.js";
import item_logpurchase from "../master-item/modules/item_logpurchase.js?t=1278ses3";

import customer_new from "../master-customer/modules/customer_new.js?abfscd21das243e";
import customer_address from "../master-customer/modules/customer_address.js?abfsdascd21243e";
import system from "../assets/js/system.js?t=asd";

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
        offer: offer,
        offer_new: offer_new,
        sales: sales,
        sales_new: sales_new,
        unit: unit,
        unit_new: unit_new,
        pack: pack,
        pack_new: pack_new,
        item_logpurchase: item_logpurchase,

        customer_new: customer_new,
        address: customer_address,
        system: system
    }
});