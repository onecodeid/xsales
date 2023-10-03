// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, lastmonth_date, current_date, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"

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

        view: false,

        // Tab List
        tab_01: TAB_01,
        tab_02: TAB_02,
        tabs: [TAB_01, TAB_02],
        selected_tab: TAB_01,

        journals: [],
        selected_journal: null,
        total_journal: 0,
        s_date: "2023-01-01",
        e_date: "2023-03-31",
        current_page: 1,
        total_journal_page: 1,
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
                jtype: 'J.RN'
            }

            context.dispatch("system/postme", {
                url: "trans/journal/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["journals", d.records])
                    context.commit("set_object", ["total_journal", d.total])
                    context.commit("set_object", ["total_journal_page", d.total_page])
                }
            }, { root: true })
        },

        // async search_account(context) {
        //     let prm = {
        //         search: context.state.search,
        //         page: 1,
        //         limit: 1000
        //     }

        //     context.dispatch("system/postme", {
        //         url: "master/account/search",
        //         prm: prm,
        //         callback: function(d) {
        //             let accs = []
        //             for (let acc of d.records) {
        //                 if (acc.parent == "0") acc.parent = false
        //                 accs.push(acc)
        //             }

        //             context.commit("set_object", ["accounts", accs])
        //         }
        //     }, { root: true })
        // },

        // async search_account_cash(context) {
        //     let prm = {
        //         search: context.state.search,
        //         page: 1,
        //         group_id: 1,
        //         limit: 1000
        //     }

        //     return context.dispatch("system/postme", {
        //         url: "master/account/search",
        //         prm: prm,
        //         callback: function(d) {
        //             let accs = []
        //             for (let acc of d.records) {
        //                 if (acc.parent == "0") acc.parent = false
        //                 accs.push(acc)
        //             }

        //             context.commit("set_object", ["cash_accounts", accs])
        //         }
        //     }, { root: true })
        // },

        // async search_tag(context) {
        //     let prm = {
        //         search: '',
        //         page: 1
        //     }

        //     context.dispatch("system/postme", {
        //         url: "master/tag/search",
        //         prm: prm,
        //         callback: function(d) {
        //             context.commit("set_object", ['tags', d.records])

        //             let tags = []
        //             for (let r of d.records) tags.push(r.tag_name)
        //             context.commit("set_object", ['tagnames', tags])
        //         }
        //     }, { root: true })
        // },

        async del(context) {
            let prm = {
                token: one_token(),
                journal_id: context.state.selected_journal.journal_id
            }

            context.dispatch("system/postme", {
                url: "trans/journal/del",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        // async search_id(context) {
        //     let prm = {
        //         token: one_token(),
        //         cash_id: context.state.cash_id
        //     }

        //     return context.dispatch("system/postme", {
        //         url: "finance/cash/search_id",
        //         prm: prm,
        //         callback: function(d) {
        //             context.commit("set_object", ["selected_cash", d])
        //             return d
        //         }
        //     }, { root: true })
        // }
    }
}