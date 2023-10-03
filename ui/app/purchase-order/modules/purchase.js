// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_purchase.js"
import { one_token, veryStartDate, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',
        token: one_token(),

        purchases: [],
        total_purchase: 0,
        total_purchase_page: 0,
        selected_purchase: {},

        s_date: veryStartDate,
        e_date: current_date(),

        current_page: 1,

        xprm: null
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

        update_purchases(state, data) {
            state.purchases = data
        },

        set_selected_purchase(state, val) {
            state.selected_purchase = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_purchase(state, val) {
            state.total_purchase = val
        },

        update_total_purchase_page(state, val) {
            state.total_purchase_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_xprm(state, val) {
            state.xprm = val
        }
    },
    actions: {
        async search(context, xprm) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date
            }

            if (xprm) {
                prm = Object.assign(prm, xprm)
                context.commit('set_xprm', xprm)
            } else if (!!context.state.xprm) {
                prm = Object.assign(prm, context.state.xprm)
            }


            let x = context.dispatch("system/postme", {
                url: "purchase/purchase/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_purchases", d.records)
                    context.commit("update_total_purchase", d.total)
                    context.commit("update_total_purchase_page", d.total_page)
                }
            }, { root: true })

            return x
        },

        async del(context) {
            let prm = {
                id: context.state.selected_purchase.purchase_id
            }

            context.dispatch("system/postme", {
                url: "purchase/purchase/del",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_purchase.purchase_id
            }

            context.dispatch("system/postme", {
                url: "trans/purchase/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        }
    }
}