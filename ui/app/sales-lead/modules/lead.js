// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
import { one_token, veryStartDate, current_date, lastmonth_date, one_user, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        URL: URL,

        leads: [],
        total_sales: 0,
        total_lead_page: 0,
        selected_lead: {},
        selected_staff: null,

        s_date: veryStartDate,
        e_date: current_date(),

        user: one_user(),

        current_page: 1
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

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_leads(state, data) {
            state.leads = data
        },

        set_selected_lead(state, val) {
            state.selected_lead = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_sales(state, val) {
            state.total_sales = val
        },

        update_total_lead_page(state, val) {
            state.total_lead_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
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
                staff_id: context.state.selected_staff ? context.state.selected_staff.staff_id : 0
            }

            let gs = ["ONE.GROUP.AVENGER", "ONE.GROUP.ADMIN"]
            if (gs.indexOf(context.state.user.group_code) > -1)
                prm.staff_id = context.state.user.staff_id

            context.dispatch("system/postme", {
                url: "sales/lead/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_leads", d.records)
                    context.commit("update_total_sales", d.total)
                    context.commit("update_total_lead_page", d.total_page)

                    context.dispatch("lead_new/search_prospect", null, { root: true })
                }
            }, { root: true })
        },

        async del(context) {
            let prm = {
                id: context.state.selected_lead.lead_id
            }

            context.dispatch("system/postme", {
                url: "sales/lead/del",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        }
    }
}