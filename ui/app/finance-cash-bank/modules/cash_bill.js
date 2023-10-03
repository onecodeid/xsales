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

        accounts: [],
        payments: [],
        total_payment: 0,

        payment_id: 0,
        payment_note: '',
        // payment_receipt: '',
        payment_date: current_date(),
        payment_post: "N",

        details: [],
        tmp_details: [],
        detail_default: { bill: null, amount: 0, post: 'N', disc: 'N', is_retur: 'N', retur: null, type: null, dp: null },
        disc: { amount: 0, post: 'N' },

        returs: [],
        dps: [],

        suppliers: [],
        selected_supplier: null,
        search_supplier: '',

        bills: [],
        selected_bill: null
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

        set_tmp_details(state, val) {
            state.tmp_details = val
        },

        set_accounts(state, val) {
            state.accounts = val
        },

        set_suppliers(state, val) {
            state.suppliers = val
        },

        set_selected_supplier(state, val) {
            state.selected_supplier = val
        },

        set_bills(state, val) {
            state.bills = val
        },

        set_selected_bill(state, val) {
            state.set_selected_bill = val
        },

        set_selected_account(state, val) {
            state.set_selected_account = val
        },

        set_returs(state, val) {
            state.returs = val
        },

        set_dps(state, val) {
            state.dps = val
        }
    },
    actions: {
        async save(context) {
            context.commit('cash_invoice/set_common', ['save', true], { root: true })
            let ds = []
            for (let d of context.state.details)
                if ((d.amount) && !!d.bill)
                    ds.push({
                        amount: d.amount,
                        bill: d.bill.bill_id,
                        disc: d.disc,
                        is_retur: d.is_retur ? d.is_retur : 'N',
                        retur: d.retur ? d.retur.retur_id : 0,
                        type: d.type.paymentdetail_id,
                        dp: d.dp ? d.dp.dp_id : 0
                    })
                    // ds.push({amount:d.amount,invoice:d.invoice.invoice_id,disc:d.disc,is_retur:d.is_retur?d.is_retur:'N',
                    //    retur:d.retur?d.retur.retur_id:0})

            let ci = context.rootState.cash_invoice

            let tags = []
            if (!!ci.selected_tags) {
                for (let d of ci.selected_tags)
                    tags.push({ tag_id: d.tag_id, tag_name: d.tag_name })
            }

            let prm = {
                hdata: JSON.stringify({
                    date: ci.payment_date,
                    note: ci.payment_note,
                    supplier: context.state.selected_supplier.vendor_id,
                    bank_account_id: ci.selected_bank_account ? ci.selected_bank_account.account_id : 0,
                    bank_id: ci.selected_bank ? ci.selected_bank.bank_id : 0,
                    giro_date: ci.giro_date ? ci.giro_date : '0000-00-00',
                    giro_number: ci.giro_number,
                    transfer_date: ci.transfer_date ? ci.transfer_date : '0000-00-00',
                    account_id: context.rootState.cash.selected_account.M_AccountID,
                    journal_type: context.rootState.cash.selected_journaltype.journaltype_code,
                    tags: tags
                }),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.payment_id = ci.payment_id

            context.dispatch("system/postme", {
                url: "finance/billpayment/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('billpayment/search', null, { root: true })
                    context.commit('cash_invoice/set_common', ['dialog_new', false], { root: true })
                    context.commit('cash_invoice/set_common', ['save', false], { root: true })
                    context.dispatch('journal_detail/search', null, { root: true })
                    context.dispatch('cash/search', {}, { root: true })
                }
            }, { root: true })
        },

        async search_supplier(context) {
            let prm = { search: context.state.search_supplier }

            context.dispatch("system/postme", {
                url: "admin/supplier/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_suppliers", d)
                }
            }, { root: true })
        },

        async search_bill(context) {
            let prm = {
                search: context.state.search,
                vendor_id: context.state.selected_supplier ? context.state.selected_supplier.vendor_id : 0
            }

            let edits = []
            if (context.state.edit) {
                for (let d of context.state.tmp_details)
                    edits.push(d.bill.bill_id)
                prm.edits = edits.join(',')
            }

            context.dispatch("system/postme", {
                url: "finance/bill/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_bills", d)
                        // context.dispatch("search_retur")
                    context.dispatch("search_dp")
                    if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, { root: true })
        },

        async search_retur(context) {
            let prm = {
                search: context.state.search,
                vendor_id: context.state.selected_supplier ? context.state.selected_supplier.vendor_id : 0
            }

            let edits = []
            if (context.state.edit) {
                for (let d of context.state.tmp_details)
                    if (d.retur)
                        if (d.retur.retur_id != 0)
                            edits.push(d.retur.retur_id)
                prm.edits = edits.join(',')
            }

            context.dispatch("system/postme", {
                url: "purchase/purchaseretur/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_returs", d)
                    if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, { root: true })
        },

        async search_dp(context) {
            let prm = {
                search: context.state.search,
                vendor_id: context.state.selected_supplier ? context.state.selected_supplier.vendor_id : 0
            }

            let edits = []
                // if (context.state.edit) {
                //     for (let d of context.state.tmp_details)
                //         if (d.dp)
                //             if (d.retur.retur_id != 0)
                //                 edits.push(d.retur.retur_id)
                //     prm.edits = edits.join(',')
                // }

            context.dispatch("system/postme", {
                url: "finance/billdp/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_dps", d)
                        // if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, { root: true })
        },

        async edit(context, prm) {
            context.dispatch("system/postme", {
                url: "finance/billpayment/get",
                prm: prm,
                callback: function(d) {
                    context.commit("cash_invoice/set_common", ['edit', true], { root: true })
                    context.commit("set_common", ['edit', true])
                    context.commit('set_tmp_details', d.details)
                    context.dispatch('search_bill')
                    context.commit('cash_invoice/set_common', ['payment_id', d.payment_id], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_date', d.payment_date], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_note', d.payment_note], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_post', d.payment_post], { root: true })
                    context.commit('cash_invoice/set_common', ['giro_date', d.giro_date], { root: true })
                    context.commit('cash_invoice/set_common', ['transfer_date', d.transfer_date], { root: true })
                    context.commit('cash_invoice/set_common', ['giro_number', d.giro_number], { root: true })

                    // TAGS
                    context.commit('cash_invoice/set_object', ['selected_tags', context.rootState.journal_detail.selected_journal.journal_tags], { root: true })


                    context.commit('set_suppliers', [d.supplier])
                    context.commit('set_selected_supplier', d.supplier)
                        // for (let c of context.rootState.cash_invoice.suppliers)
                        //     if (c.supplier_id == d.supplier_id)
                        // context.commit('cash_invoice/set_selected_supplier', c, {root:true})

                    for (let a of context.rootState.cash_invoice.bank_accounts)
                        if (a.account_id == d.bank_account_id)
                            context.commit("cash_invoice/set_selected_bank_account", a, { root: true })

                    for (let b of context.rootState.cash_invoice.banks)
                        if (b.bank_id == d.bank_id)
                            context.commit("cash_invoice/set_selected_bank", b, { root: true })

                    context.commit("cash_invoice/set_common", ['dialog_new', true], { root: true })

                }
            }, { root: true })
        }
    }
}