// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_unit.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',
        
        selected_unit: {},
        dialog_new: false,

        unit_name: "",
        unit_code: ""
    },
    mutations: {
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
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

        set_dialog_new (state, v) {
            state.dialog_new = v
        }
    },
    actions: {
        async save(context) {
            
            try {
                let prm = {token : one_token(),
                            unit_name: context.state.unit_name,
                            unit_code: context.state.unit_code
                        }

                if (context.state.edit)
                        prm.unit_id = context.rootState.unit.selected_unit.unit_id

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.unit)
                        context.dispatch("unit/search", {}, {root:true})
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_unit', {unit_id:resp.data}, {root:true})
                        context.dispatch('item_new/search_unit', {}, {root:true})
                    }
                    if (!!context.rootState.sales_new) {
                        context.dispatch('sales_new/search_unit', {}, {root:true})
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