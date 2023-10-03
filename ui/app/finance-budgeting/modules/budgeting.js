// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_account.js"
import { one_token, veryStartDate, current_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: {},
        selected_budget: {},

        s_date: veryStartDate,
        e_date: current_date(),
        months: [],
        selected_start_month: null,
        selected_end_month: null,

        years: [],
        selected_year: null,

        current_page: 1,
        snackbar: false,
        snackbar_text: 'Snackbar Text'
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
            state[v[0]] = v[1]
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
                sdate: context.state.selected_start_month.sdate,
                edate: context.state.selected_end_month.edate
            }

            return context.dispatch("system/postme", {
                url: "finance/budgeting/search",
                prm: prm,
                callback: function(d) {

                    context.commit("set_object", ["accounts", d.records])
                    // context.commit("update_total_account", d.total)
                    // context.commit("update_total_account_page", d.total_page)

                    return d.records
                }
            }, { root: true })
        },

        async search_months(context) {
            let prm = {
                year: context.state.selected_year.year_value
            }

            return context.dispatch("system/postme", {
                // url: "report/report/search_months",
                url: "report/report/search_months_in_year",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["months", d])
                    return d
                }
            }, { root: true })
        },

        async search_years(context) {
            let prm = {}

            return context.dispatch("system/postme", {
                url: "report/report/search_years",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["years", d])
                    return d
                }
            }, { root: true })
        },

        async save(context) {
            let budgets = []
            for (let a of context.state.accounts)
                for (let b of a.budgets)
                    if (parseFloat(b.budget)>0) budgets.push({account:a.account_id,date:b.date,budget:b.budget})
            let prm = {
                token: one_token(),
                jdata: JSON.stringify(budgets)
            }

            return context.dispatch("system/postme", {
                url: "finance/budgeting/save",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        },

        async saveBudget(context) {
            let __s = context.state, __a = __s.selected_account, d = __s.selected_budget.date.split('-')
            let prm = {
                token: one_token(),
                account: __a.account_id,
                year: d[0],
                month: d[1],
                budget: __s.selected_budget.budget
            }

            return context.dispatch("system/postme", {
                url: "finance/budgeting/save_budget",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        },

        async copyTo(context, prm) {
            let __s = context.state, __a = __s.selected_account
            prm.token=one_token()

            return context.dispatch("system/postme", {
                url: "finance/budgeting/copy_to",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        },

        // async del(context, prm) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         prm.token = one_token()
        //         prm.id = context.state.selected_account.M_AccountID

        //         let resp = await api.del(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)

        //             context.commit("set_common", ["snackbar_text", resp.message])
        //             context.commit("set_object", ["snackbar", true])
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")

        //             context.dispatch('search', {})
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // }
    }
}