// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_cash.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        search_invoice: '',

        accounts: [],
        total_account: 0,

        account_name: 0,
        account_code: '',

        dialog_new: false
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

        update_search_status(state, val) {
            state.search_status = val
        }
    },
    actions: {
        async save(context, fn) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                prm.account_name = context.state.account_name
                prm.account_code = context.state.account_code

                if (context.state.edit)
                    prm.account_id = context.rootState.account.selected_account.M_AccountID

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    if (fn) {
                        fn()
                    } else
                        context.dispatch('cash/search', {}, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                }
                return resp
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
            }
        }
    }
}