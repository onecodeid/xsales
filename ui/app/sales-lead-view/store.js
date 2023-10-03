// State
//  data ...
// Mutations
//
//
// Actions
import lead from "../sales-lead/modules/lead.js?31343123";
import lead_new from "../sales-lead/modules/lead_new.js?123";
// import customer from "../master-customer/modules/customer.js";
import customer_new from "../master-customer/modules/customer_new.js?abcd21243e";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false,
        dialog_confirm: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL",
        view: true
    },

    mutations : {
        set_dialog_delete (state, v) {
            state.dialog_delete = v
        },

        set_dialog_confirm (state, v) {
            state.dialog_confirm = v
        }
    },

    modules : {
        lead: lead,
        lead_new: lead_new,
        customer_new: customer_new,
        system: system
    }
});
