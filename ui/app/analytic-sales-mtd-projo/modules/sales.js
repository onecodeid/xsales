// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,

        sales: [],
        total_sales: 0,
        selected_sales: {},

        pagination: { sortBy: "staff_name", descending: false, rowsPerPage: -1 },

        current_page: 1,
        sdate: new Date().toISOString().substr(0, 10),
        edate: new Date().toISOString().substr(0, 10)
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

        set_sales(state, data) {
            state.sales = data
        },

        set_selected_sales(state, val) {
            state.selected_sales = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_total_sales(state, val) {
            state.total_sales = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_sdate(state, val) {
            state.sdate = val
        },

        set_edate(state, val) {
            state.edate = val
        },

        set_pagination(state, v) {
            state.pagination = v
        }
    },
    actions: {
        async search(context) {
            context.commit("update_search_status", 1)

            let prm = {}
            prm.token = one_token()
            prm.date = context.state.edate

            context.dispatch("system/postme", {
                url: "analytic/sales/mtd_projo",
                prm: prm,
                callback: function(d) {
                    context.commit("set_sales", d.records)
                    context.commit("set_total_sales", d.total)
                }
            }, { root: true })
        }
    }
}