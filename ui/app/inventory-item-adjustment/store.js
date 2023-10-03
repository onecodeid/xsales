// State
//  data ...
// Mutations
//
//
// Actions
import adjustment from "./modules/adjustment.js?t=assd";
import adjustment_new from "./modules/adjustment_new.js?t=asd";
// import doctor from "./modules/doctor.js";
// import payment from "./modules/payment.js";
// import order from "./modules/order.js";
// import area from "./modules/area.js";
// import other from "./modules/other.js";
// import photo from "./modules/photo.js";
import system from "../assets/js/system.js";

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
        adjustment: adjustment,
        adjustment_new: adjustment_new,
        //  doctor: doctor,
        //  payment: payment,
        //  order: order,
        //  area: area,
        //  other: other,
        //  photo: photo,
        system: system
    }
});