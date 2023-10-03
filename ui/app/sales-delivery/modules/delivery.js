// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_delivery.js"
import { one_token, veryStartDate, current_date, lastmonth_date, URL, HOST } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        URL: URL,
        HOST: HOST,

        deliverys: [],
        total_delivery: 0,
        total_delivery_page: 0,
        selected_delivery: {},

        s_date: veryStartDate,
        e_date: current_date(),

        current_page: 1,

        invoice_note: '',
        dialog_invoice: false,
        invoice_save: false,

        dialog_confirm: false,
        dialog_calendar: false,
        confirm_save: false,

        snackbar: false,
        snackbar_text: ''
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

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_deliverys(state, data) {
            state.deliverys = data
        },

        set_selected_delivery(state, val) {
            state.selected_delivery = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_delivery(state, val) {
            state.total_delivery = val
        },

        update_total_delivery_page(state, val) {
            state.total_delivery_page = val
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
                edate: context.state.e_date
            }

            context.dispatch("system/postme", {
                url: "sales/delivery/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_deliverys", d.records)
                    context.commit("update_total_delivery", d.total)
                    context.commit("update_total_delivery_page", d.total_page)
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

        //             context.commit("update_deliverys", data.records)
        //             context.commit("update_total_delivery", data.total)
        //             context.commit("update_total_delivery_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                delivery_id: context.state.selected_delivery.delivery_id
            }

            context.dispatch("system/postme", {
                url: "sales/delivery/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async confirm(context) {
            let prm = {
                delivery_id: context.state.selected_delivery.delivery_id
            }

            context.commit('set_common', ['confirm_save', true])
            context.dispatch("system/postme", {
                url: "sales/delivery/confirm",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['confirm_save', false])
                    context.commit('set_common', ['dialog_confirm', false])
                    context.dispatch("search")
                },
                failback: function(e) {
                    context.commit('set_common', ['confirm_save', false])

                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', e])
                }
            }, { root: true })
        },

        async invoice(context) {
            let prm = {
                jdata: JSON.stringify([context.state.selected_delivery.delivery_id]),
                hdata: JSON.stringify({ note: context.state.invoice_note, customer: context.state.selected_delivery.customer_id })
            }

            context.commit('set_common', ['invoice_save', true])
            context.dispatch("system/postme", {
                url: "sales/delivery/invoice_create",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['invoice_save', false])
                    context.commit('set_common', ['dialog_invoice', false])
                    context.dispatch("search")
                },
                failback: function(e) {
                    context.commit('set_common', ['invoice_save', false])
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_delivery.delivery_id
            }

            context.dispatch("system/postme", {
                url: "trans/delivery/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        }
    }
}