// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_expedition.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_expedition: {},
        dialog_new: false,

        expedition_name: "",
        expedition_code: "",
        expedition_address: "",
        expedition_destination: "",
        expedition_website: "",
        expedition_phone1: "",
        expedition_phone2: "",
        expedition_note: ""
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
                    expedition_name: context.state.expedition_name,
                    expedition_code: context.state.expedition_code,
                    expedition_address: context.state.expedition_address,
                    expedition_phone1: context.state.expedition_phone1,
                    expedition_phone2: context.state.expedition_phone2,
                    expedition_destination: context.state.expedition_destination,
                    expedition_note: context.state.expedition_note,
                    expedition_website: context.state.expedition_website
                }

                if (context.state.edit)
                    prm.expedition_id = context.rootState.expedition.selected_expedition.expedition_id

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.expedition)
                        context.dispatch("expedition/search", {}, { root: true })
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_expedition', { expedition_id: resp.data }, { root: true })
                        context.dispatch('item_new/search_expedition', {}, { root: true })
                    }
                    if (!!context.rootState.sales_new) {
                        context.dispatch('sales_new/search_expedition', {}, { root: true })
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