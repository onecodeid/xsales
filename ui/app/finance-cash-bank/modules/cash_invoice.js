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
        search_bank: '',
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
        detail_default: { invoice: null, amount: 0, post: 'N', disc: 'N', is_retur: 'N', retur: null },
        disc: { amount: 0, post: 'N' },

        customers: [],
        selected_customer: null,
        search_customer: '',

        invoices: [],
        selected_invoice: null,

        returs: [],

        selected_account: null,
        types: { cash: 1 },

        bank_accounts: [],
        selected_bank_account: null,
        banks: [],
        selected_bank: null,
        account_number: '',

        giro_number: '',
        giro_date: current_date(),
        transfer_date: current_date(),

        selected_tags: null,
        selected_tagnames: null,

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

        set_object(state, v) {
            let name = v[0]
            let val = v[1]
            state[name] = val
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

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
        },

        set_invoices(state, val) {
            state.invoices = val
        },

        set_selected_invoice(state, val) {
            state.set_selected_invoice = val
        },

        set_returs(state, val) {
            state.returs = val
        },

        set_selected_account(state, val) {
            state.set_selected_account = val
        },

        set_bank_accounts(state, val) {
            state.bank_accounts = val
        },

        set_selected_bank_account(state, val) {
            state.selected_bank_account = val
        },

        set_banks(state, val) {
            state.banks = val
        },

        set_selected_bank(state, val) {
            state.selected_bank = val
        },

        set_payment_note(state, val) {
            state.payment_note = val
        }
    },
    actions: {
        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/account/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if ((d.amount) && !!d.invoice)
                    ds.push({
                        amount: d.amount,
                        invoice: d.invoice.invoice_id,
                        disc: d.disc,
                        is_retur: d.is_retur ? d.is_retur : 'N',
                        retur: d.retur ? d.retur.retur_id : 0
                    })

            let tags = []
            let found = false
                // if (!!context.state.selected_tags) {
            if (!!context.state.selected_tagnames) {
                // for (let d of context.state.selected_tags)
                for (let t of context.state.selected_tagnames) {
                    found = false
                    for (let d of context.rootState.cash.tags) {
                        if (t == d.tag_name) {
                            tags.push({ tag_id: d.tag_id, tag_name: d.tag_name })
                            found = !found
                        }
                    }

                    if (!found)
                        tags.push({ tag_id: 0, tag_name: t })
                }
            }

            let prm = {
                hdata: JSON.stringify({
                    date: context.state.payment_date,
                    note: context.state.payment_note,
                    customer: context.state.selected_customer.customer_id,
                    bank_account_id: context.state.selected_bank_account ? context.state.selected_bank_account.account_id : 0,
                    bank_id: context.state.selected_bank ? context.state.selected_bank.bank_id : 0,
                    giro_date: context.state.giro_date ? context.state.giro_date : "0000-00-00",
                    giro_number: context.state.giro_number,
                    transfer_date: context.state.transfer_date ? context.state.transfer_date : "0000-00-00",
                    account_id: context.rootState.cash.selected_account.M_AccountID,
                    journal_type: context.rootState.cash.selected_journaltype.journaltype_code,
                    tags: tags
                }),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.payment_id = context.state.payment_id

            context.dispatch("system/postme", {
                url: "finance/payment/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('payment/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                    context.dispatch('journal_detail/search', null, { root: true })
                    context.dispatch('cash/search', {}, { root: true })
                },
                failback: function(e) {
                    context.commit('cash/set_common', ['snackbar', true], { root: true })
                    context.commit('cash/set_common', ['snackbar_text', e], { root: true })
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        async search_customer(context) {
            let prm = { customer_name: context.state.search_customer }

            context.dispatch("system/postme", {
                url: "master/customer/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_customers", d)
                }
            }, { root: true })
        },

        async search_invoice(context) {
            let prm = {
                search: context.state.search,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0
            }

            let edits = []
                // console.log(context.state.tmp_details)
            if (context.state.edit) {
                for (let d of context.state.tmp_details)
                    edits.push(d.invoice.invoice_id)
                prm.edits = edits.join(',')
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/search_autocomplete",
                prm: prm,
                callback: function(d) {

                    context.commit("set_invoices", d)
                    context.dispatch("search_retur")
                        // if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, { root: true })
        },

        async search_retur(context) {
            let prm = {
                search: context.state.search,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0
            }

            let edits = []
                // console.log(context.state.tmp_details)
            if (context.state.edit) {
                for (let d of context.state.tmp_details)
                    if (d.retur)
                        if (d.retur.retur_id != 0)
                            edits.push(d.retur.retur_id)
                prm.edits = edits.join(',')
            }

            context.dispatch("system/postme", {
                url: "sales/retur/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_returs", d)
                    if (context.state.edit) context.commit("set_details", context.state.tmp_details)
                }
            }, { root: true })
        },

        async search_bank_account(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "admin/bankaccount/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_bank_accounts", d)
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

        async edit(context, prm) {
            context.dispatch("system/postme", {
                url: "finance/payment/get",
                prm: prm,
                callback: function(d) {
                    // console.log(x)
                    // let d = x.payment

                    // context.commit('set_selected_payment', d)
                    if (d.giro_date == "0000-00-00")
                        d.giro_date = null
                    context.commit('set_common', ['payment_id', d.payment_id])
                    context.commit('set_common', ['payment_date', d.payment_date])
                    context.commit('set_payment_note', d.payment_note)
                    context.commit('set_common', ['payment_post', d.payment_post])
                    context.commit('set_common', ['giro_date', d.giro_date])
                    context.commit('set_common', ['transfer_date', d.transfer_date])
                    context.commit('set_common', ['giro_number', d.giro_number])

                    // TAGS
                    context.commit('set_object', ['selected_tags', context.rootState.journal_detail.selected_journal.journal_tags])
                    let tags = []
                    for (let t of context.rootState.journal_detail.selected_journal.journal_tags) tags.push(t.tag_name)
                    context.commit('set_object', ['selected_tagnames', tags])
                    context.commit('set_tmp_details', d.details)

                    let c = { customer_id: d.customer_id, customer_name: d.customer_name, city_name: d.city_name }
                    context.commit('set_customers', [c])
                        // for (let c of context.state.customers)
                        //     if (c.customer_id == d.customer_id)
                    context.commit('set_selected_customer', c)

                    for (let a of context.state.bank_accounts)
                        if (a.account_id == d.bank_account_id)
                            context.commit("set_selected_bank_account", a)

                    for (let b of context.state.banks)
                        if (b.bank_id == d.bank_id)
                            context.commit("set_selected_bank", b)

                    console.log(context.state.tmp_details)
                    context.commit("set_common", ['dialog_new', true])
                    context.commit("set_common", ['edit', true])

                    context.dispatch('search_invoice')
                }
            }, { root: true })
        }
    }
}