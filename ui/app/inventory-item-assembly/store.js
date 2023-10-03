// State
//  data ...
// Mutations
//
//
// Actions
import assembly from "./modules/assembly.js?t=1234";
import assembly_new from "./modules/assembly_new.js?t=1234";
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
        dialog_confirm: false
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_confirm(state, v) {
            state.dialog_confirm = v
        }
    },

    modules: {
        assembly: assembly,
        assembly_new: assembly_new,
        //  doctor: doctor,
        //  payment: payment,
        //  order: order,
        //  area: area,
        //  other: other,
        //  photo: photo,
        system: system
    }
});