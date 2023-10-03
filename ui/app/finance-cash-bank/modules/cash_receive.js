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

        dialog_new: false,

        payment_date: current_date(),
        payment_post: false,
        payment_note: '',

        details: [],
        detail_default: { account: null, amount: 0, post: 'N' },
        tmp_details: [],

        accounts: [],

        banks: [],
        selected_bank: null,

        giro_number: '',
        giro_date: current_date(),
        transfer_date: current_date(),

        out: false
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

        set_accounts(state, v) {
            state.accounts = v
        },

        set_details(state, v) {
            state.details = v
        },

        set_tmp_details(state, v) {
            state.tmp_details = v
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
            context.commit('cash_invoice/set_common', ['save', true], { root: true })
            let ds = []
            for (let d of context.state.details)
                if ((d.amount) && !!d.account)
                    ds.push({ amount: d.amount, account: d.account.account_id })



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
                    bank_account_id: ci.selected_bank_account ? ci.selected_bank_account.account_id : 0,
                    bank_id: ci.selected_bank ? ci.selected_bank.bank_id : 0,
                    giro_date: ci.giro_date,
                    giro_number: ci.giro_number,
                    transfer_date: ci.transfer_date,
                    account_id: context.rootState.cash.selected_account.M_AccountID,
                    journal_type: context.rootState.cash.selected_journaltype.journaltype_code,
                    tags: tags
                }),
                jdata: JSON.stringify(ds),
                out: context.state.out
            }
            if (context.state.edit) prm.payment_id = ci.payment_id

            context.dispatch("system/postme", {
                url: "finance/receive/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('payment/search', null, { root: true })
                    context.commit('cash_invoice/set_common', ['dialog_new', false], { root: true })
                    context.commit('cash_invoice/set_common', ['save', false], { root: true })
                    context.dispatch('journal_detail/search', null, { root: true })
                    context.dispatch('cash/search', {}, { root: true })
                }
            }, { root: true })
        },

        async edit(context, prm) {
            context.dispatch("system/postme", {
                url: "finance/receive/get",
                prm: prm,
                callback: function(d) {
                    context.commit("set_common", ['edit', true])
                        // console.log(x)
                        // let d = x.payment
                        // context.dispatch('search_invoice')
                        // context.commit('set_selected_payment', d)
                    context.commit('cash_invoice/set_common', ['payment_id', d.payment_id], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_date', d.payment_date], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_note', d.payment_note], { root: true })
                    context.commit('cash_invoice/set_common', ['payment_post', d.payment_post], { root: true })
                    context.commit('cash_invoice/set_common', ['giro_date', d.giro_date], { root: true })
                    context.commit('cash_invoice/set_common', ['transfer_date', d.transfer_date], { root: true })
                    context.commit('cash_invoice/set_common', ['giro_number', d.giro_number], { root: true })

                    // TAGS
                    context.commit('cash_invoice/set_object', ['selected_tags', context.rootState.journal_detail.selected_journal.journal_tags], { root: true })
                    context.commit('set_details', d.details)

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