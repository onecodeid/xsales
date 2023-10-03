// State
//  data ...
// Mutations
//
//
// Actions
import delivery from "./modules/delivery.js?t=12werassd1d3";
import delivery_new from "./modules/delivery_new.js?t=ssfsa23";
import delivery_calendar from "./modules/delivery_calendar.js?t=w1sdfa23";
import sales from "../sales-order/modules/sales.js?t=2d1g3";
import sales_new from "../sales-order/modules/sales_new.js?t=23d1assd3";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
        dialog_progress: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL",
        selected_page_size: null
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

        set_selected_page_size(state, v) {
            state.selected_page_size = v
        },

        set_dialog_progress(state, v) {
            state.dialog_progress = v
        }
    },

    modules: {
        delivery: delivery,
        delivery_new: delivery_new,
        delivery_calendar: delivery_calendar,
        sales: sales,
        sales_new: sales_new,
        system: system
    }
});