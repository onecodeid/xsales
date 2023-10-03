// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_customer.js"
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        customers: [],
        total_customer: 0,
        total_customer_page: 0,
        selected_customer: {},

        xcustomers: [],
        selected_xcustomer: null,

        current_page: 1,
        unmap: 'N'
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

        update_customers(state, data) {
            state.customers = data
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_customer(state, val) {
            state.total_customer = val
        },

        update_total_customer_page(state, val) {
            state.total_customer_page = val
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
                unmap: context.state.unmap
            }

            context.dispatch("system/postme", {
                url: "tmp/tmp_sales_import_customer/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["customers", d.records])
                    context.commit("set_object", ["total_customer", d.total])
                    context.commit("set_object", ["total_customer_page", d.total_page])
                }
            }, { root: true })
        },

        async search_customer(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/customer/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["xcustomers", d.records])
                }
            }, { root: true })

            return x
        },

        async save(context) {
            let prm = {
                id: context.state.selected_customer.id,
                b: context.state.selected_xcustomer.customer_name,
                c: context.state.selected_xcustomer.customer_id
            }

            let x = context.dispatch("system/postme", {
                url: "tmp/tmp_sales_import_customer/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('search')
                }
            }, { root: true })

            return x
        },

        // async del(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = { token: one_token(), id: context.state.selected_customer.customer_id }


        //         let resp = await api.del(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")

        //             context.dispatch('search', {})
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // }
    }
}