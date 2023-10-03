import disc from "./modules/disc.js"; // Mengganti "affiliate" menjadi "disc"
import disc_new from "./modules/disc_new.js"; // Mengganti "affiliate_new" menjadi "disc_new"
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
        disc: disc, // Mengganti "affiliate" menjadi "disc"
        disc_new: disc_new, // Mengganti "affiliate_new" menjadi "disc_new"
        system: system
    }
});
``
