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

        account_name: '',
        account_code: '',
        account_code_prefix: '',
        account_parent: 0,
        account_from: 0,
        account_pos: 'D',

        groups: [],
        selected_group: null,

        accounts: [],
        selected_account: null,

        dialog_new: false,

        balance: 0,
        balanceDebit: 0,
        balanceCredit: 0,
        dialog_balance: false,

        actions: [{ id: "LAST", text: "DI DALAM Akun" }, { id: "AFTER", text: "SETELAH Akun" }],
        selected_action: null,

        save: false,
        edit: false
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

        update_search_status(state, val) {
            state.search_status = val
        }
    },
    actions: {
        async save_new(context, fn) {
            let prm = {
                token: one_token(),
                hdata: JSON.stringify({
                    account_name: context.state.account_name,
                    account_group: context.state.selected_group.group_id,
                    account_parent: context.state.selected_account ? context.state.selected_account.account_id : 0,
                    account_pos: context.state.account_pos
                })
            }
            if (context.state.edit)
                prm.account_id = context.rootState.account.selected_account.M_AccountID

            context.dispatch("system/postme", {
                url: "master/account/save_new",
                prm: prm,
                callback: function(d) {
                    context.dispatch('account/search', {}, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                }
            }, { root: true })
        },

        async set_balance(context) {
            context.commit('set_common', ['save', true])

            let side = context.rootState.account.selected_account.M_AccountType
            let prm = {
                account_id: context.rootState.account.selected_account.M_AccountID,
                jdata: JSON.stringify({ 'open_debit': side == 'A' ? context.state.balance : 0, 'open_credit': side == 'A' ? 0 : context.state.balance })
            }
            if (Math.round(context.state.balance < 0))
                prm.jdata = JSON.stringify({ 'open_debit': side == 'A' ? 0 : context.state.balance, 'open_credit': side == 'A' ? context.state.balance : 0 })

            // Overwrite
            prm.jdata = JSON.stringify({ 'open_debit': context.state.balanceDebit, 'open_credit': context.state.balanceCredit })

            context.dispatch("system/postme", {
                url: "trans/balance/set",
                prm: prm,
                callback: function(d) {
                    context.dispatch('account/search', {}, { root: true })
                    context.commit('set_common', ['dialog_balance', false])
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        async search_group(context) {
            let prm = {
                search: '',
                limit: 99999
            }

            context.dispatch("system/postme", {
                url: "master/accountgroup/search",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['groups', d.records])
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: '',
                page: 1,
                limit: 100,
                group_id: context.state.selected_group.group_id,
                isparent: 'Y'
            }

            context.dispatch("system/postme", {
                url: "master/account/search_for_parent",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['accounts', d.records])
                    console.log(d.records)
                }
            }, { root: true })
        },

        // async save(context, fn) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = {}
        //         prm.token = one_token()
        //         prm.account_name = context.state.account_name
        //         prm.account_code = context.state.account_code
        //         prm.account_group = context.state.selected_group.group_id
        //         prm.account_prefix = context.state.account_code_prefix
        //         prm.account_parent = context.state.account_parent
        //         prm.account_from = context.state.account_from

        //         console.log("from")
        //         console.log(prm.account_from)

        //         if (context.state.edit)
        //             prm.account_id = context.rootState.account.selected_account.M_AccountID

        //         let resp = await api.save(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")

        //             if (fn) {
        //                 fn()
        //             } else
        //                 context.dispatch('account/search', {}, { root: true })
        //             context.commit('set_common', ['dialog_new', false])
        //         }
        //         return resp
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //     }
        // },
    }
}