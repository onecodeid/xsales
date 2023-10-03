// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_invoice.js"
import { one_token, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        balances: [],
        total_balance: 0,
        total_balance_page: 0,
        selected_balance: null,

        accounts: [],
        selected_account: null,

        s_date: lastmonth_date(),
        e_date: current_date(),
        // selected_customer: null,

        selected_journal: null,
        journal_details: [],

        current_page: 1
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
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                account_id: context.state.selected_account ? context.state.selected_account.account_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_fin_001/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["balances", d])
                        // context.commit("set_object", ["total_balance", d.total])
                        // context.commit("set_object", ["total_ledger_page", d.total_page])
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.state.s_date,
                "edate=" + context.state.e_date,
                "account_id=" + (context.state.selected_account ? context.state.selected_account.account_id : 0)
            ];
            return prm.join("&")
        }
    }
}