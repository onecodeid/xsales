// State
//  data ...
// Mutations
//
//
// Actions
import profile from "./modules/profile.js";
// import item_new from "./modules/item_new.js";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state : {
        dialog_delete: false
    },

    mutations : {
        set_dialog_delete (state, v) {
            state.dialog_delete = v
        }
    },

    modules : {
        profile: profile,
        // item_new: item_new,
         system: system
    }
});
