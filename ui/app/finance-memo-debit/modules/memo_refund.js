// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, lastmonth_date, current_date } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,
        dialog_new: false,
        current_date: current_date(),
        refund_date: lastmonth_date(),

        selected_account: null,
        memo_id: 0,
        memo_number: '',
        memo_amount: 0,
        memo_used: 0,
        memo_refunded: 0,
        refund_amount: 0,
        refund_id: 0,
        refund_note: '',
        refund_number: '',

        selected_memo: null,
        selected_customer: null
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
        }
    },
    actions: {
        async save(context) {
            let prm = {
                token: one_token(),
                refund_id: context.state.refund_id,
                hdata: JSON.stringify({
                    refund_date: context.state.refund_date,
                    refund_amount: context.state.refund_amount,
                    refund_note: context.state.refund_note,
                    refund_credit_account: context.state.selected_account ? context.state.selected_account.account_id : 0,
                    memo_id: context.state.memo_id
                })
            }

            return context.dispatch("system/postme", {
                url: "finance/memo/refund",
                prm: prm,
                callback: function(d) {
                    // context.commit("set_object", ["selected_memo", d])
                    return d
                },
                failback: function(e) {
                    alert(e)
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                refund_id: context.state.refund_id
            }

            return context.dispatch("system/postme", {
                url: "finance/memorefund/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_memo", d])
                    return d
                }
            }, { root: true })
        },

        async search_memo_id(context) {
            let prm = {
                token: one_token(),
                memo_id: context.state.memo_id
            }

            return context.dispatch("system/postme", {
                url: "finance/memo/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_memo", d])
                    return d
                }
            }, { root: true })
        }
    }
}