// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_bill.js"
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

        bills: [],
        total_bill: 0,

        dp_id: 0,
        dp_note: '',
        dp_number: '',
        dp_ppn: 'Y',
        dp_date: current_date(),
        dp_giro_date: current_date(),
        dp_transfer_date: current_date(),
        dp_giro_number: '',
        dp_amount: 0,

        vendors: [],
        selected_vendor: null,

        warehouses: [],
        selected_warehouse: null,

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

        update_search_status(state, val) {
            state.search_status = val
        },

        set_details(state, val) {
            state.details = val
        },

        set_items(state, val) {
            state.items = val
        },

        set_dp_note(state, val) {
            state.dp_note = val
        },

        set_vendors(state, val) {
            state.vendors = val
        },

        set_selected_vendor(state, val) {
            state.selected_vendor = val
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
        }
    },
    actions: {
        async search_bankaccount(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/bankaccount/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_bankaccounts", d)
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: '',
                page: 1,
                group_id: 1,
                limit: 25
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, { root: true })
        },

        async search_bank(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/bank/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_banks", d)
                }
            }, { root: true })
        },

        async search_vendor(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/vendor/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_vendors", d.records)
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: '%',
                page: 1,
                vendor_id: context.state.selected_vendor.vendor_id
            }

            context.dispatch("system/postme", {
                url: "purchase/receive/search_dd",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_items", d)
                }
            }, { root: true })
        },

        async search_paymenttype(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/paymenttype/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_paymenttypes", d)
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let cs = context.state
            let hdata = {
                dp_date: context.state.dp_date,
                // dp_transfer_date: context.state.dp_transfer_date,
                // dp_giro_date: context.state.dp_giro_date,
                // dp_giro_number: context.state.dp_giro_number,
                dp_note: context.state.dp_note,
                dp_vendor: cs.selected_vendor.vendor_id,
                dp_amount: context.state.dp_amount,
                dp_payment_type: cs.selected_paymenttype ? cs.selected_paymenttype.paymenttype_id : 0,
                dp_bank: cs.selected_bank ? cs.selected_bank.bank_id : 0,
                dp_bank_account: cs.selected_bankaccount ? cs.selected_bankaccount.account_id : 0,
                dp_account: cs.selected_account ? cs.selected_account.M_AccountID : 0
            }

            if (!!context.state.dp_giro_date)
                hdata.dp_giro_date = context.state.dp_giro_date
            if (!!context.state.dp_giro_number)
                hdata.dp_giro_number = context.state.dp_giro_number
            if (!!context.state.dp_transfer_date)
                hdata.dp_transfer_date = context.state.dp_transfer_date

            let prm = {
                hdata: JSON.stringify(hdata)
            }
            if (context.state.edit) prm.dp_id = context.state.dp_id

            context.dispatch("system/postme", {
                url: "finance/billdp/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('dp/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        }
    }
}