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

        items: [],
        total_item: 0,
        total_item_page: 0,
        selected_item: null,

        accounts: [],
        selected_account: null,
        selected_accounts: [],

        s_date: lastmonth_date(),
        e_date: current_date(),

        selected_warehouse: null,

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
            let ids = []
            for (let ac of context.state.selected_accounts) ids.push(ac.account_id)

            let prm = {
                token: one_token(),
                search: context.state.search,
                // page: context.state.current_page,
                sdate: context.state.s_date,
                // edate: context.state.e_date,
                warehouse_id: context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0,
                // account_ids: ids.join(',')
            }

            context.dispatch("system/postme", {
                url: "report/one_iv_003/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["items", d])
                        // context.commit("set_object", ["total_ledger", d.total])
                        // context.commit("set_object", ["total_ledger_page", d.total_page])
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.state.s_date,
                // "edate=" + context.state.e_date,
                "warehouse_id=" + (context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0)
            ];
            return prm.join("&")
        },

        async searchJournalDetail(context) {
            let prm = {
                token: one_token(),
                journal_id: context.state.selected_journal.journal_id
            }

            return context.dispatch("system/postme", {
                url: "trans/journal/get_detail",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        }
    }
}