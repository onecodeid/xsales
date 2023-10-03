// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_invoice.js"
import { one_token, veryStartDate, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        logs: [],
        total_log: 0,
        total_log_page: 0,
        selected_log: null,

        s_date: veryStartDate,
        e_date: current_date(),

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
                page: context.state.current_page
            }

            context.dispatch("system/postme", {
                url: "report/one_log_001/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["logs", d.records])
                    context.commit("set_object", ["total_log_page", d.total_page])
                }
            }, { root: true })
        },

        // async collect(context) {
        //     let prm = [
        //         "token=" + one_token(),
        //         "search=" + context.state.search,
        //         "sdate=" + context.state.s_date,
        //         "edate=" + context.state.e_date,
        //         "warehouse_id=" + (context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0)
        //     ];
        //     return prm.join("&")
        // },

        // async searchJournalDetail(context) {
        //     let prm = {
        //         token: one_token(),
        //         journal_id: context.state.selected_journal.journal_id
        //     }

        //     return context.dispatch("system/postme", {
        //         url: "trans/journal/get_detail",
        //         prm: prm,
        //         callback: function(d) {
        //             return d
        //         }
        //     }, { root: true })
        // }
    }
}