// State
//  data ...
// Mutations
//
//
// Actions
import sales from "./modules/sales.js?t=123";
// import adjustment_new from "./modules/adjustment_new.js";
// import doctor from "./modules/doctor.js";
// import payment from "./modules/payment.js";
// import order from "./modules/order.js";
// import area from "./modules/area.js";
// import other from "./modules/other.js";
// import photo from "./modules/photo.js";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        }
    },

    modules: {
        sales: sales,
        // adjustment_new: adjustment_new,
        //  doctor: doctor,
        //  payment: payment,
        //  order: order,
        //  area: area,
        //  other: other,
        //  photo: photo,
        system: system
    }
});