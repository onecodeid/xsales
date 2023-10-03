// State
//  data ...
// Mutations
//
//
// Actions
import setting from "./modules/setting.js";
// import targetomzet from "../finance-target-omzet/modules/targetomzet.js";
import system from "../assets/js/system.js?t=wer";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        }
    },

    modules: {
        setting: setting,
        // targetomzet: targetomzet,
        system: system
    }
});