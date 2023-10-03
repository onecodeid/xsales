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

        accounts: [],
        selected_account: null,

        s_date: lastmonth_date(),
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
                page: context.state.current_page,
                // sdate: "2022-12-01",
                // edate: "2022-12-31"
                sdate: context.rootState.report_param.selected_month2.sdate,
                edate: context.rootState.report_param.selected_month2.edate
            }
            if (context.rootState.report_param.selected_month2.sdate == "1971-01-01") {
                prm.sdate = context.state.s_date
                prm.edate = context.state.e_date
            }

            return context.dispatch("system/postme", {
                url: "report/one_fin_003_2/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d])
                    return d
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "sdate=" + (context.rootState.report_param.selected_month2.sdate == "1971-01-01" ? context.state.s_date : context.rootState.report_param.selected_month2.sdate),
                "edate=" + (context.rootState.report_param.selected_month2.sdate == "1971-01-01" ? context.state.e_date : context.rootState.report_param.selected_month2.edate)
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