import { one_token, URL, lastmonth_date, current_date } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search: '',
        edit: false,
        dialog_new: false,
        sa: false,
        snackbar: false,

        accounts: [],
        selected_account: null,

        journal_date: current_date(),
        journal_number: '',
        journal_note: '',
        journal_id: 0,

        details: [],
        detail_default: { account: null, credit: 0, debit: 0 },
        detail_cnt: 8
    },
    mutations: {
        set_object(state, v) {
            let name = v[0]
            let val = v[1]
            state[name] = val
        }
    },
    actions: {
        async save(context) {
            let accs = []
            for (let d of context.state.details) {
                if (!!d.account && (parseFloat(d.debit) > 0 || parseFloat(d.credit) > 0))
                    accs.push({ account: d.account.account_id, debit: d.debit, credit: d.credit })
            }

            let prm = {
                journal_date: context.state.journal_date,
                journal_note: context.state.journal_note,
                journal_receipt: '',
                journal_tags: context.rootState.tag.selected_tagnames ? JSON.stringify(context.rootState.tag.selected_tagnames) : "[]",
                jdata: JSON.stringify(accs)
            }

            if (context.state.edit) prm.journal_id = context.state.journal_id
            return context.dispatch("system/postme", {
                url: "trans/journal/save",
                prm: prm,
                callback: function(d) {
                    return d
                },
                failback: function(e) {
                    return e
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                // group_id: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let rcd = []
                    for (let r of d.records) {
                        if (r.parent == "0") r.parent = false
                        rcd.push(r)
                    }
                    context.commit("set_object", ["accounts", rcd])
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                journal_id: context.state.journal_id
            }

            return context.dispatch("system/postme", {
                url: "trans/journal/search_id",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        }
    }
}