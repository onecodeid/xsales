// State
//  data ...
// Mutations
//
//
// Actions
import item from "./modules/item.js";
import item_new from "./modules/item_new.js?t=1278ses3";
import unit from "../master-unit/modules/unit.js?t=2";
import unit_new from "../master-unit/modules/unit_new.js?t=w";
import pack from "../master-pack/modules/pack.js";
import pack_new from "../master-pack/modules/pack_new.js?t=w";
import category_new from "../master-category/modules/category_new.js";
import system from "../assets/js/system.js?d=787";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_print: false,
        report_url: '',
        view: false
    },

    mutations : {
        set_dialog_delete (state, v) {
            state.dialog_delete = v
        },

        set_dialog_print (state, v) {
            state.dialog_print = v
        },

        set_report_url (state, v) {
            state.report_url = v
        }
    },

    modules : {
        item: item,
        item_new: item_new,
        unit: unit,
        unit_new: unit_new,
        pack: pack,
        pack_new: pack_new,
        category_new: category_new,
        system: system
    }
});
