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
        current_date: current_date(),
        edit: false,
        save: false,
        
        accounts: [],
        payments: [],
        total_payment: 0,
        
        payment_id: 0,
        payment_note: '',
        // payment_receipt: '',
        payment_date: current_date(),

        details: [],
        detail_default: {invoice:null,cash:0,transfer:0,giro:0,cheque:0,post:'N'},
        disc: {cash:0,transfer:0,giro:0,cheque:0,post:'N'},

        customers: [],
        selected_customer: null,
        search_customer: '',

        invoices: [],
        selected_invoice: null,

        dialog_new: false
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

        update_search_status(state, val) {
            state.search_status = val
        },

        set_details(state, val) {
            state.details = val
        },

        set_accounts(state, val) {
            state.accounts = val
        },

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.set_selected_customer = val
        },

        set_invoices(state, val) {
            state.invoices = val
        },

        set_selected_invoice(state, val) {
            state.set_selected_invoice = val
        }
    },
    actions: {
        async search_account(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/account/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, {root:true})
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if ((d.credit > 0 || d.debit > 0 ) && !!d.account)
                    ds.push({credit:d.credit,debit:d.debit,account:d.account.account_id})

            let prm = {
                payment_date: context.state.payment_date,
                payment_note: context.state.payment_note,
                // payment_receipt: context.state.payment_receipt,
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.payment_id = context.state.payment_id

            context.dispatch("system/postme", {
                url:"trans/payment/save",
                prm:prm,
                callback:function(d) {
                    context.dispatch('payment/search', null, {root:true})
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, {root:true})
        },

        async search_customer(context) {
            let prm = { search : context.state.search_customer }

            context.dispatch("system/postme", {
                url:"admin/customer/search_autocomplete",
                prm:prm,
                callback:function(d) {
                    context.commit("set_customers", d)
                }
            }, {root:true})
        },

        async search_invoice(context) {
            let prm = { 
                search : context.state.search_customer,
                customer_id: context.state.selected_customer?context.state.selected_customer.customer_id:0
            }

            context.dispatch("system/postme", {
                url:"sales/invoice/search_autocomplete",
                prm:prm,
                callback:function(d) {
                    context.commit("set_invoices", d)
                }
            }, {root:true})
        }
    }
}