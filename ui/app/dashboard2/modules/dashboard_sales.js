// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, one_user, URL, veryStartDate, first_date, lastmonth_date, current_date, TAB_01, TAB_02 } from "../../assets/js/global.js?t=123"
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
        first_date: first_date(),

        customer001: {},
        sales002: [],
        sales003: [],

        profile: {},

        months2: [],
        selected_month2: null,

        staffs: [],
        selected_staff: null
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
        async searchSalesCustomer001(context) {
            let prm = {
                search: context.state.search,
                edate: context.state.selected_month2.edate, //current_date,
                sdate: context.state.selected_month2.sdate, //first_date,
                staff_id: context.state.selected_staff?context.state.selected_staff.staff_id:0
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard_sales/sales_customer_001",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['customer001', d])
                    
                    return d
                }
            }, { root: true })
        },

        async search_months(context) {
            return context.dispatch("system/postme", {
                url: "report/report/search_months",
                prm: {},
                callback: function(d) {
                    context.commit("set_object", ["months2", d])
                    return d
                }
            }, { root: true })
        },

        async search_staff(context) {
            let prm = {
                search: '',
                position: 'POS.ADMIN',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "systm/staff/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["staffs", d.records])
                    return d.records
                }
            }, { root: true })
        },

        async searchSales002(context) {
            let prm = {
                search: context.state.search,
                edate: context.state.selected_month2.edate, //current_date,
                sdate: context.state.selected_month2.sdate, //first_date,
                staff_id: context.state.selected_staff?context.state.selected_staff.staff_id:0
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard_sales/sales_002",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['sales002', d])
                }
            }, { root: true })
        },

        async searchSales003(context) {
            let prm = {
                search: context.state.search,
                edate: context.state.selected_month2.edate, //current_date,
                sdate: context.state.selected_month2.sdate, //first_date,
                staff_id: context.state.selected_staff?context.state.selected_staff.staff_id:0
            }

            return context.dispatch("system/postme", {
                url: "dashboard/dashboard_sales/sales_003",
                prm: prm,
                callback: function(d) {
                    d.state = true
                    context.commit('set_object', ['sales003', d])
                    console.log(d)
                }
            }, { root: true })
        }
    }
}