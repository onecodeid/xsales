// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, lastmonth_date, current_date, veryStartDate, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        search_md5: '',
        query: '',
        URL: URL,
        current_date: current_date(),
        import: false,

        view: false,

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

        cash_type_code: 'CASH.RECEIVE',
        cash_type_name: '',

        cashes: [],
        selected_cash: null,
        total_cash: 0,
        s_date: veryStartDate,
        e_date: current_date(),
        current_page: 1,
        total_cash_page: 1,
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

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                md5: context.state.search_md5,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                account_id: context.rootState.cash.selected_account ? context.rootState.cash.selected_account.account_id : 0
            }

            context.dispatch("system/postme", {
                url: (!!context.state.import ? "finance/cash/search_history" : "finance/cash/search"),
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["cashes", d.records])
                    context.commit("set_object", ["total_cash", d.total])
                    context.commit("set_object", ["total_cash_page", d.total_page])
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
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

                    context.commit("set_object", ["accounts", accs])
                    return d.records
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
                cash_id: context.state.selected_cash.cash_id
            }

            context.dispatch("system/postme", {
                url: "finance/cash/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                cash_id: context.state.cash_id
            }

            return context.dispatch("system/postme", {
                url: "finance/cash/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_cash", d])
                    return d
                }
            }, { root: true })
        }
    }
}