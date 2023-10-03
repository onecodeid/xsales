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

        search_detail: '',
        dialog_detail: false,

        // invoices: [],
        // total_invoice: 0,
        // total_invoice_page: 0,
        // selected_invoice: {},

        records: [],
        selected_record: null,

        selected_account: null,

        details: [],
        selected_detail: null,
        total_detail: 0,
        total_detail_page: 1,
        current_detail_page: 1,

        s_date: lastmonth_date(),
        e_date: current_date(),
        // selected_customer: null,

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
                // page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                // customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_fin_008_2/search",
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
                search: context.state.search_detail,
                page: context.state.current_detail_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                code: context.state.selected_account.code
            }

            context.dispatch("system/postme", {
                url: "report/one_fin_008_2/search_detail",
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
                "sdate=" + context.state.s_date,
                "edate=" + context.state.e_date,
                "customer_id=" + (context.state.selected_customer ? context.state.selected_customer.customer_id : 0)
            ];
            return prm.join("&")
        }
    }
}