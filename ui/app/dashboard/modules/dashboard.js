// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_dashboard.js"
import { one_token, one_user, URL } from "../../assets/js/global.js"
import * as chart from "../../../assets/js/chart.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        user: one_user(),
        URL: URL,

        ipaymu_balance: 0,
        admin_total_fee: 0,
        admin_fee_sdate: '',
        admin_fee_edate: '',

        omzets: [],
        omzet_high: 100,

        omzet_sdate: moment().subtract(30, 'days').format('YYYY-MM-DD'),
        omzet_edate: moment().format('YYYY-MM-DD'),
        omzet_more: true,

        target_current: 181450000,
        target_weekly: 290000000,

        cust_current: 0,
        cust_prev: 0,

        profile: {},

        omzet_admin: {}
    },
    mutations: {
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_omzets (state, val) {
            state.omzet_high = 100
            if (val.length > 0)
                state.omzet_high = val[0].omzet
            
            for (let i in val)
                val[i].scale = Math.round((val[i].omzet / state.omzet_high) * 100)
            state.omzets = val
        },

        set_profile (state, val) {
            state.profile = val
        },

        set_omzet_admin (state, val) {
            state.omzet_admin = val
        }
    },
    actions: {
        async ipaymu_balance(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {token:one_token()}
                let resp = await api.ipaymu_balance(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_common', ['ipaymu_balance', resp.data])
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async admin_total_fee(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {token:one_token()}
                let resp = await api.admin_total_fee(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_common', ['admin_total_fee', resp.data.total])
                    context.commit('set_common', ['admin_fee_sdate', resp.data.sdate])
                    context.commit('set_common', ['admin_fee_edate', resp.data.edate])
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async omzet_per_product(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {token:one_token()}
                prm.sdate = context.state.omzet_sdate
                prm.edate = context.state.omzet_edate
                let resp = await api.omzet_per_product(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_omzets', resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },
        
        async profile(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.profile(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_profile', resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async get_target_this_week(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.get_target_this_week(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_common', ['target_weekly', resp.data.omzet_target])
                    context.commit('set_common', ['target_current', resp.data.omzet_current])
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async get_customer_total_new(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.get_customer_total_new(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    console.log(resp)
                    context.commit('set_common', ['cust_current', resp.data.cust_current])
                    context.commit('set_common', ['cust_prev', resp.data.cust_prev])
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async get_omzet_admin(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.get_omzet_admin(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok', resp)
                    context.commit('set_omzet_admin', resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_status_not_ok (context, d) {
            context.commit("set_common", ['search_status', 3])
            context.commit("update_search_error_message", d.message)
        },

        async search_status_ok (context) {
            context.commit("set_common", ['search_status', 2])
            context.commit("update_search_error_message", "")
        }
    }
}