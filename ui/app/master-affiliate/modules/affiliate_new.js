// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_affiliate.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_affiliate: {},
        dialog_new: false,

        affiliate_name: "",
        affiliate_number: ""
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
                    affiliate_name: context.state.affiliate_name,
                    affiliate_number: context.state.affiliate_number
                }

                if (context.state.edit)
                    prm.affiliate_id = context.rootState.affiliate.selected_affiliate.affiliate_id

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.affiliate)
                        context.dispatch("affiliate/search", {}, { root: true })
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_affiliate', { affiliate_id: resp.data }, { root: true })
                        context.dispatch('item_new/search_affiliate', {}, { root: true })
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