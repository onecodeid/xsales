// State
//  data ...
// Mutations
//
//
// Actions
import driver from "./modules/driver.js";
import driver_new from "./modules/driver_new.js?t=asd";
import system from "../assets/js/system.js?d=787";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        report_url: ''
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
        driver: driver,
        driver_new: driver_new,
        system: system
    }
});