// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_journal.js"
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

        accounts: [],
        journals: [],
        total_journal: 0,

        journal_id: 0,
        journal_note: '',
        journal_receipt: '',
        journal_date: current_date(),

        details: [],
        detail_default: { account: null, credit: 0, debit: 0, post: 'N' },

        tags: [],

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

        set_accounts(state, val) {
            state.accounts = val
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
                if ((d.credit > 0 || d.debit > 0) && !!d.account)
                    ds.push({ credit: d.credit, debit: d.debit, account: d.account.account_id })

            let prm = {
                journal_date: context.state.journal_date,
                journal_note: context.state.journal_note,
                journal_receipt: context.state.journal_receipt,
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.journal_id = context.state.journal_id

            context.dispatch("system/postme", {
                url: "trans/journal/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('journal/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        }
    }
}