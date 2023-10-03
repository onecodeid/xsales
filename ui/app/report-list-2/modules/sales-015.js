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

        invoices: [],
        total_invoice: 0,
        total_invoice_page: 0,
        selected_invoice: {},

        s_date: veryStartDate,
        e_date: current_date(),
        selected_customer: null,

        current_page: 1,
        payment_histories: [],
        dialog_history: false
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
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_sales_015/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["invoices", d.records])
                    context.commit("set_object", ["total_invoice", d.total])
                    context.commit("set_object", ["total_invoice_page", d.total_page])
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
        },

        async searchHistory(context) {
            let prm = {
                token: one_token(),
                invoice_id: context.state.selected_invoice.invoice_id
            }

            context.dispatch("system/postme", {
                url: "finance/pay2/get_history",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["payment_histories", d])
                }
            }, { root: true })
        },
    }
}