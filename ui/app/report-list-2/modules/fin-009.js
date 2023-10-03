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
        dialog_detail: false,

        records: {},
        selected_record: null,

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
                search: "",
                page: 1,
                // sdate: context.rootState.report_param.selected_endyear.sdate
                sdate: context.rootState.report_param.selected_month2.sdate,
                edate: context.rootState.report_param.selected_month2.edate
            }

            context.dispatch("system/postme", {
                url: "report/one_fin_009/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["records", d.records])
                        // context.commit("set_object", ["total_invoice", d.total])
                        // context.commit("set_object", ["total_invoice_page", d.total_page])
                }
            }, { root: true })
        },

        async searchDetail(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_detail_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                account_report_id: context.state.selected_account.rptid
            }

            context.dispatch("system/postme", {
                url: "report/one_fin_008/search_detail",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_object", ["details", d.records])
                    context.commit("set_object", ["total_detail", d.total])
                    context.commit("set_object", ["total_detail_page", d.total_page])
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.rootState.report_param.selected_month2.sdate,
                "edate=" + context.rootState.report_param.selected_month2.edate
            ];
            return prm.join("&")
        }
    }
}