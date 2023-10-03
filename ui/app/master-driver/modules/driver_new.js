// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_driver.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_driver: {},
        dialog_new: false,

        driver_name: "",
        driver_plat: "",
        driver_vehicle: "",
        driver_pool: "",
        driver_weight: "",
        driver_phone1: "",
        driver_phone2: "",
        driver_note: ""
    },
    mutations: {
        set_common(state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
        },

        set_object(state, v) {
            let name = v[0]
            let val = v[1]
            state[name] = val
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_dialog_new(state, v) {
            state.dialog_new = v
        }
    },
    actions: {
        async save(context) {

            try {
                let prm = {
                    token: one_token(),
                    driver_name: context.state.driver_name,
                    driver_plat: context.state.driver_plat,
                    driver_vehicle: context.state.driver_vehicle,
                    driver_phone1: context.state.driver_phone1,
                    driver_phone2: context.state.driver_phone2,
                    driver_pool: context.state.driver_pool,
                    driver_weight: context.state.driver_weight,
                    driver_note: context.state.driver_note
                }

                if (context.state.edit)
                    prm.driver_id = context.rootState.driver.selected_driver.driver_id

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.driver)
                        context.dispatch("driver/search", {}, { root: true })
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_driver', { driver_id: resp.data }, { root: true })
                        context.dispatch('item_new/search_driver', {}, { root: true })
                    }
                    if (!!context.rootState.sales_new) {
                        context.dispatch('sales_new/search_driver', {}, { root: true })
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}