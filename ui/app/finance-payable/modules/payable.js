import { one_token, URL, lastmonth_date, current_date, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"

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
        tmp_pay_id: 0

        // memos: [],
        // selected_memo: null,
        // total_memo: 0,
        // s_date: lastmonth_date(),
        // e_date: current_date(),
        // current_page: 1,
        // total_memo_page: 1,
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
        }
    }
}