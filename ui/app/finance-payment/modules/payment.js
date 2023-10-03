// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_payment.js"
import { one_token, current_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        
        payments: [],
        total_payment: 0,
        total_payment_page: 0,
        selected_payment: {},

        s_date: current_date(),
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

        update_payments(state, data) {
            state.payments = data
        },

        set_selected_payment(state, val) {
            state.selected_payment = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_payment(state, val) {
            state.total_payment = val
        },

        update_total_payment_page(state, val) {
            state.total_payment_page = val
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
                url:"finance/payment/search",
                prm:prm,
                callback:function(d) {
                    context.commit("update_payments", d.records)
                    context.commit("update_total_payment", d.total)
                    context.commit("update_total_payment_page", d.total_page)
                }
            }, {root:true})
        },

        async del(context) {
            let prm = {
                id : context.state.selected_payment.payment_id
            }

            context.dispatch("system/postme", {
                url:"finance/payment/del",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        },

        async post(context) {
            let prm = {
                id : context.state.selected_payment.payment_id
            }

            context.dispatch("system/postme", {
                url:"finance/payment/post",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        }
    }
}