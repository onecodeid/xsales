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
        account_id: 0,
        account: null,

        view: false,

        journals: [],
        selected_journal: null,
        total_journal: 0,

        s_date: veryStartDate,
        e_date: current_date(),
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
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                account_id: context.state.account_id
            }

            context.dispatch("system/postme", {
                url: "trans/journal/search_by_account",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["journals", d.records])
                    context.commit("set_object", ["total_journal", d.total])
                    context.commit("set_object", ["total_journal_page", d.total_page])
                }
            }, { root: true })
        }
    }
}