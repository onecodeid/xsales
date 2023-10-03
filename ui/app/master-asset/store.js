// State
//  data ...
// Mutations
//
//
// Actions
import asset from "./modules/asset.js";
import asset_new from "./modules/asset_new.js";
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
        asset: asset,
        asset_new: asset_new,
        system: system
    }
});
