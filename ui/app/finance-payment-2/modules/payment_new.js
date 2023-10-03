// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_payment.js"
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
        view: false,
        
        payments: [],
        total_payment: 0,
        
        payment_id: 0,
        payment_note: '',
        payment_number: '',
        payment_ppn: 'Y',
        payment_date: current_date(),
        payment_giro_date: current_date(),
        payment_transfer_date: current_date(),
        payment_giro_number: '',
        payment_amount: 0,

        details: [],
        tmp_details: [],
        detail_default: {invoice:null,amount:0,post:'N',disc:'N',is_retur:'N',retur:null},

        invoices: [],

        customers: [],
        selected_customer: null,

        items: [],
        selected_item: null,

        paymenttypes: [],
        selected_paymenttype: null,

        banks: [],
        selected_bank: null,

        bankaccounts: [],
        selected_bankaccount: null,

        accounts: [],
        selected_account: null,

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

        set_items(state, val) {
            state.items = val
        },

        set_payment_note(state, val) {
            state.payment_note = val
        },

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
        },

        set_banks(state, val) {
            state.banks = val
        },

        set_selected_bank(state, val) {
            state.selected_bank = val
        },

        set_bankaccounts(state, val) {
            state.bankaccounts = val
        },

        set_selected_bankaccount(state, val) {
            state.selected_bankaccount = val
        },

        set_accounts(state, val) {
            state.accounts = val
        },

        set_selected_account(state, val) {
            state.selected_account = val
        },

        set_paymenttypes(state, val) {
            state.paymenttypes = val
        },

        set_selected_paymenttype(state, val) {
            state.selected_paymenttype = val
        },

        set_invoices(state, val) {
            state.invoices = val
        },

        set_details(state, val) {
            state.details = val
        },

        set_tmp_details(state, val) {
            state.tmp_details = val
        }
    },
    actions: {
        async search_bankaccount(context) {
            let prm = {
                search : '',
                page : 1,
                token : one_token(),
                group_id : 1
            }

            context.dispatch("system/postme", {
                url:"master/bankaccount/search_autocomplete",
                prm:prm,
                callback:function(d) {
                    context.commit("set_bankaccounts", d)
                }
            }, {root:true})
        },

        async search_account(context) {
            let prm = {
                search : '',
                page : 1,
                group_id : 1
            }

            context.dispatch("system/postme", {
                url:"master/account/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, {root:true})
        },

        async search_bank(context) {
            let prm = {
                search : '',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/bank/search_autocomplete",
                prm:prm,
                callback:function(d) {
                    context.commit("set_banks", d)
                }
            }, {root:true})
        },

        async search_customer(context) {
            let prm = { customer_name : ''/*context.state.search_customer*/ }

            context.dispatch("system/postme", {
                url:"master/customer/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_customers", d.records)
                }
            }, {root:true})
        },

        async search_item(context) {
            let prm = {
                search : '%',
                page : 1,
                customer_id : context.state.selected_customer.customer_id
            }

            context.dispatch("system/postme", {
                url:"purchase/receive/search_dd",
                prm:prm,
                callback:function(d) {
                    console.log(d)
                    context.commit("set_items", d)
                }
            }, {root:true})
        },

        async search_paymenttype(context) {
            let prm = {
                search : '',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/paymenttype/search_autocomplete",
                prm:prm,
                callback:function(d) {
                    context.commit("set_paymenttypes", d)
                }
            }, {root:true})
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let cs = context.state
            let hdata = {
                payment_date:context.state.payment_date,
                payment_transfer_date:context.state.payment_transfer_date,
                payment_giro_date:context.state.payment_giro_date,
                payment_giro_number:context.state.payment_giro_number,
                payment_note: context.state.payment_note,
                payment_customer: cs.selected_customer.customer_id,
                payment_amount: context.state.payment_amount,
                payment_payment_type: cs.selected_account?cs.selected_account.map_ref:0,
                // payment_payment_type: cs.selected_paymenttype?cs.selected_paymenttype.paymenttype_id:0,
                payment_bank: cs.selected_bank?cs.selected_bank.bank_id:0,
                payment_bank_account: cs.selected_account?cs.selected_account.bank_account_id:0,
                // payment_bank_account: cs.selected_bankaccount?cs.selected_bankaccount.account_id:0,
                payment_account: cs.selected_account?cs.selected_account.M_AccountID:0
                // payment_account: cs.selected_bankaccount?cs.selected_bankaccount.account_idx:0
            }

            let prm = {
                hdata: JSON.stringify(hdata)
            }
            if (context.state.edit) prm.payment_id = context.state.payment_id

            context.dispatch("system/postme", {
                url:"finance/paymentdp/save",
                prm:prm,
                callback:function(d) {
                    context.dispatch('dp/search', null, {root:true})
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, {root:true})
        },

        async search_invoice(context) {
            let prm = { 
                search : context.state.search,
                customer_id: context.state.selected_customer?context.state.selected_customer.customer_id:0
            }

            let edits = []
            // console.log(context.state.tmp_details)
            if (context.state.edit) {
                for (let d of context.state.tmp_details)
                    edits.push(d.invoice.invoice_id)
                prm.edits = edits.join(',')
            }
                
            context.dispatch("system/postme", {
                url:"sales/invoice/search_autocomplete",
                prm:prm,
                callback:function(d) {

                    context.commit("set_invoices", d)
                    // context.dispatch("search_retur")
                    // if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, {root:true})
        }
    }
}