// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
import { URL, one_token, veryStartDate, current_date, lastmonth_date, one_user } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        url: URL,

        saless: [],
        total_sales: 0,
        total_sales_page: 0,
        selected_sales: {},
        sales_id: 0,

        s_date: veryStartDate,
        e_date: current_date(),

        selected_staff: null,

        user: one_user(),
        to_edit: false,

        current_page: 1,

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

        update_saless(state, data) {
            state.saless = data
        },

        set_selected_sales(state, val) {
            state.selected_sales = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_sales(state, val) {
            state.total_sales = val
        },

        update_total_sales_page(state, val) {
            state.total_sales_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
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
                staff: context.state.selected_staff ? context.state.selected_staff.staff_id : 0
            }

            let x = context.dispatch("system/postme", {
                url: "sales/sales/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_saless", d.records)
                    context.commit("update_total_sales", d.total)
                    context.commit("update_total_sales_page", d.total_page)
                }
            }, { root: true })

            return x
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                sales_id: context.state.sales_id
            }

            return context.dispatch("system/postme", {
                url: "sales/sales/search_id",
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

        //             context.commit("update_saless", data.records)
        //             context.commit("update_total_sales", data.total)
        //             context.commit("update_total_sales_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                id: context.state.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url: "sales/sales/del",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url: "trans/sales/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        }
    }
}