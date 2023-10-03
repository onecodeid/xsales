// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, veryStartDate } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,

        items: [],
        total_item: 0,
        selected_item: {},

        warehouses: [],
        selected_warehouse: null,

        pagination: { sortBy: "invoice_date", descending: true },

        current_page: 1,
        sdate: veryStartDate, // new Date().toISOString().substr(0, 10),
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

        set_items(state, data) {
            state.items = data
        },

        set_selected_item(state, val) {
            state.selected_item = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_total_item(state, val) {
            state.total_item = val
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

        set_warehouses(state, v) {
            state.warehouses = v
        },

        set_selected_warehouse(state, v) {
            state.selected_warehouse = v
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
            prm.sdate = context.state.sdate
            prm.edate = context.state.edate
            prm.warehouse = (context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0)

            context.dispatch("system/postme", {
                url: "analytic/inventory/recapt_daily",
                prm: prm,
                callback: function(d) {
                    context.commit("set_items", d.records)
                    context.commit("set_total_item", d.total)
                }
            }, { root: true })
        },

        async search_warehouse(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/warehouse/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_warehouses", d.records)
                }
            }, { root: true })
        }
    }
}