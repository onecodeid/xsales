// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, one_user, URL, veryStartDate, lastmonth_date, current_date, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"
import * as chart from "../../../assets/js/chart.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        user: one_user(),
        URL: URL,

        very_start_date: veryStartDate,
        current_date: current_date(),
        lastmonth_date: lastmonth_date(),

        sales001: {},
        finance002: {},
        finance003: {},
        finance004: [],

        finance002_sdate: lastmonth_date(),
        finance002_edate: current_date(),
        finance003_sdate: lastmonth_date(),
        finance003_edate: current_date(),
        finance004_sdate: lastmonth_date(),
        finance004_edate: current_date(),

        ratio_liquidity: {},
        ratio_liquidity_edate: current_date(),

        ratio_activity: {},
        ratio_activity_edate: current_date(),

        margin_profitability: [],
        margin_profitability_sdate: lastmonth_date(),
        margin_profitability_edate: current_date(),


        profile: {}
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
        async search_sales001(context) {
            let prm = {
                search: context.state.search,
                edate: context.state.current_date,
                sdate: context.state.lastmonth_date
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/sales001",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['sales001', d])
                }
            }, { root: true })
        },

        async search_finance002(context) {
            let prm = {
                period: 'M',
                edate: context.state.finance002_edate,
                sdate: context.state.finance002_sdate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/finance002",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['finance002', d])
                }
            }, { root: true })
        },

        async search_finance003(context) {
            let prm = {
                period: 'M',
                edate: context.state.finance003_edate,
                sdate: context.state.finance003_sdate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/finance003",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['finance003', d])
                }
            }, { root: true })
        },

        async search_finance004(context) {
            let prm = {
                period: 'Y',
                edate: context.state.finance004_edate,
                sdate: context.state.finance004_sdate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/finance004",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['finance004', d])
                }
            }, { root: true })
        },

        async search_ratio_liquidity(context) {
            let prm = {
                sdate: context.state.very_start_date,
                edate: context.state.ratio_liquidity_edate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/ratio_liquidity",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['ratio_liquidity', d])
                }
            }, { root: true })
        },

        async search_ratio_activity(context) {
            let prm = {
                sdate: context.state.very_start_date,
                edate: context.state.ratio_activity_edate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/ratio_activity",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['ratio_activity', d])
                }
            }, { root: true })
        },

        async search_margin_profitability(context) {
            let prm = {
                sdate: context.state.margin_profitability_sdate,
                edate: context.state.margin_profitability_edate
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard2/margin_profitability",
                prm: prm,
                callback: function(d) {
                    context.commit('set_object', ['margin_profitability', d])
                }
            }, { root: true })
        }
    }
}