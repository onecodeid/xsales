// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_account.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: {},

        current_page: 1,
        snackbar: false,
        snackbar_text: 'Snackbar Text'
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
            state[v[0]] = v[1]
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_accounts(state, data) {
            state.accounts = data
        },

        set_selected_account(state, val) {
            state.selected_account = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_account(state, val) {
            state.total_account = val
        },

        update_total_account_page(state, val) {
            state.total_account_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                search: context.state.search,
                page: context.state.current_page,
                limit: 1000,
                token: one_token()
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_accounts", d.records)
                    context.commit("update_total_account", d.total)
                    context.commit("update_total_account_page", d.total_page)

                    return d.records
                }
            }, { root: true })
        },

        // async search(context, prm) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         prm.token = one_token()
        //         prm.search = context.state.search
        //         prm.page = context.state.current_page
        //         prm.limit = 1000
        //         let resp = await api.search(prm)

        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")
        //             let data = {
        //                 records: resp.data.records,
        //                 total: resp.data.total,
        //                 total_page: resp.data.total_page
        //             }

        //             context.commit("update_accounts", data.records)
        //             context.commit("update_total_account", data.total)
        //             context.commit("update_total_account_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.id = context.state.selected_account.M_AccountID

                let resp = await api.del(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)

                    context.commit("set_common", ["snackbar_text", resp.message])
                    context.commit("set_object", ["snackbar", true])
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    context.dispatch('search', {})
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}