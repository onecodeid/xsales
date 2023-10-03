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
        retur: false,

        invoices: [],
        total_invoice: 0,
        total_invoice_page: 0,
        selected_invoice: {},
        invoice_id: 0,

        s_date: veryStartDate,
        e_date: current_date(),
        due_date: current_date(),
        selected_customer: null,

        staffs: [],
        selected_staff: null,

        headerStats: [
            { title: 'Penjualan Belum Terbayar', amount: 300000000, color: 'cyan', url: 'report-list-2/one-fin-007' },
            { title: 'Penjualan Jatuh Tempo', amount: 200000000, color: 'orange', url: 'sales-invoice/due' },
            { title: 'Pelunasan 30 Hari Terakhir', amount: 150000000, color: 'green' }
        ],

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
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0,
                retur: context.state.retur ? 'Y' : 'N'
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_invoices", d.records)
                    context.commit("update_total_invoice", d.total)
                    context.commit("update_total_invoice_page", d.total_page)
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.state.s_date,
                "edate=" + context.state.e_date,
                "staff_id=" + (0)
            ];
            return prm.join("&")
        },

        async search_due(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                duedate: context.state.due_date,
                lunas: 'N',
                staff_id: context.state.selected_staff ? context.state.selected_staff.staff_id : 0
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_invoices", d.records)
                    context.commit("update_total_invoice", d.total)
                    context.commit("update_total_invoice_page", d.total_page)
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                invoice_id: context.state.invoice_id
            }

            return context.dispatch("system/postme", {
                url: "sales/invoice/search_id",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
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
                invoice_id: context.state.selected_invoice.invoice_id
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_invoice.invoice_id
            }

            context.dispatch("system/postme", {
                url: "trans/invoice/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async searchHeaderStats(context) {
            let prm = {
                token: one_token()
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/header_stats",
                prm: prm,
                callback: function(d) {
                    let x = JSON.parse(JSON.stringify(context.state.headerStats))
                    x[0].amount = d.total_unpaid
                    x[1].amount = d.total_due
                    x[2].amount = d.total_payment
                    context.commit('set_object', ['headerStats', x])
                }
            }, { root: true })
        },

        async search_staff(context) {
            let prm = {
                search: '',
                position: 'POS.ADMIN',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "systm/staff/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['staffs', d.records])
                }
            }, { root: true })
        },
    }
}