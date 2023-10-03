// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_invoice.js"
import { one_token, current_date, lastmonth_date, URL, veryStartDate } from "../../assets/js/global.js"

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

        selected_warehouse: null,

        selected_journal: null,
        journal_details: [],

        s_date: veryStartDate,
        e_date: current_date(),

        current_page: 1,
        current_tab: 0,

        analysis: {}
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
                warehouse_id: context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0,
                sdate: context.state.s_date,
                edate: context.state.e_date
            }

            return context.dispatch("system/postme", {
                url: "report/one_iv_012/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["items", d])
                    return d
                }
            }, { root: true })
        },

        async searchAnalytic(context) {
            let prm = {
                token: one_token(),
                warehouse_id: context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_iv_012/search_analytic",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['analysis', d])
                }
            }, { root: true })
        },

        async saveAnalytic(context) {
            let prm = {
                token: one_token(),
                desc: JSON.stringify(context.state.analysis)
            }

            return context.dispatch("system/postme", {
                url: "report/one_iv_012/save_analytic",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
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