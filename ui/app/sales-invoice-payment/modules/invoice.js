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
        
        invoices: [],
        total_invoice: 0,
        total_invoice_page: 0,
        selected_invoice: {},

        s_date: lastmonth_date(),
        e_date: current_date(),

        current_page: 1
    },
    mutations: {
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_invoices(state, data) {
            state.invoices = data
        },

        set_selected_invoice(state, val) {
            state.selected_invoice = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_invoice(state, val) {
            state.total_invoice = val
        },

        update_total_invoice_page(state, val) {
            state.total_invoice_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token : one_token(),
                search : context.state.search,
                page : context.state.current_page,
                sdate : context.state.s_date,
                edate : context.state.e_date
            }

            context.dispatch("system/postme", {
                url:"sales/invoice/search",
                prm:prm,
                callback:function(d) {
                    context.commit("update_invoices", d.records)
                    context.commit("update_total_invoice", d.total)
                    context.commit("update_total_invoice_page", d.total_page)
                }
            }, {root:true})
        },

        // async searchx(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = {}
        //         prm.token = one_token()
        //         prm.search = context.state.search
        //         prm.page = context.state.current_page
        //         prm.sdate = context.state.s_date
        //         prm.edate = context.state.e_date
        //         let resp = await api.search(prm)
                
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")
        //             let data = {
        //                 records: resp.data.records,
        //                 total: resp.data.total,
        //                 total_page: resp.data.total_page
        //             }

        //             context.commit("update_invoices", data.records)
        //             context.commit("update_total_invoice", data.total)
        //             context.commit("update_total_invoice_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                invoice_id : context.state.selected_invoice.invoice_id
            }

            context.dispatch("system/postme", {
                url:"sales/invoice/delete",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        },

        async post(context) {
            let prm = {
                id : context.state.selected_invoice.invoice_id
            }

            context.dispatch("system/postme", {
                url:"trans/invoice/post",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        }
    }
}