// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, veryStartDate, lastmonth_date, current_date, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,
        current_date: current_date(),

        // Tab List
        tab_01: TAB_01,
        tab_02: TAB_02,
        tabs: [TAB_01, TAB_02],
        selected_tab: TAB_01,

        accounts: [],
        cash_accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: null,
        tmp_acc_id: 0,

        memos: [],
        selected_memo: null,
        total_memo: 0,
        s_date: veryStartDate,
        e_date: current_date(),
        current_page: 1,
        total_memo_page: 1,
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
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                // account_id: context.rootState.memo.selected_account.account_id
            }

            context.dispatch("system/postme", {
                url: "finance/memo/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["memos", d.records])
                    context.commit("set_object", ["total_memo", d.total])
                    context.commit("set_object", ["total_memo_page", d.total_page])
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let accs = []
                    for (let acc of d.records) {
                        if (acc.parent == "0") acc.parent = false
                        accs.push(acc)
                    }

                    context.commit("set_object", ["accounts", accs])
                }
            }, { root: true })
        },

        async search_account_cash(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let accs = []
                    for (let acc of d.records) {
                        if (acc.parent == "0") acc.parent = false
                        accs.push(acc)
                    }

                    context.commit("set_object", ["cash_accounts", accs])
                }
            }, { root: true })
        },

        async search_tag(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/tag/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['tags', d.records])

                    let tags = []
                    for (let r of d.records) tags.push(r.tag_name)
                    context.commit("set_object", ['tagnames', tags])
                }
            }, { root: true })
        },

        async del(context) {
            let prm = {
                token: one_token(),
                memo_id: context.state.selected_memo.memo_id
            }

            context.dispatch("system/postme", {
                url: "finance/memo/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                memo_id: context.state.memo_id
            }

            return context.dispatch("system/postme", {
                url: "finance/memo/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_memo", d])
                    return d
                }
            }, { root: true })
        }
    }
}