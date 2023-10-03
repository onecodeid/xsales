// State
//  data ...
// Mutations
//
//
// Actions
import pack from "./modules/pack.js";
import pack_new from "./modules/pack_new.js?t=asds";
// import doctor from "./modules/doctor.js";
// import payment from "./modules/payment.js";
// import order from "./modules/order.js";
// import area from "./modules/area.js";
// import other from "./modules/other.js";
// import photo from "./modules/photo.js";
import system from "../assets/js/system.js?d=787";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_print: false,
        report_url: ''
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
        pack: pack,
        pack_new: pack_new,
        system: system
    }
});
