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

        items2: [],
        total_item2: 0,
        selected_item2: {},

        warehouses: [],
        selected_warehouse: null,

        pagination: { sortBy: "omzet_freq", descending: true },

        current_page: 1,
        sdate: veryStartDate, // new Date().toISOString().substr(0, 10),
        edate: new Date().toISOString().substr(0, 10),
        sdate2: veryStartDate, // new Date().toISOString().substr(0, 10),
        edate2: new Date().toISOString().substr(0, 10),

        highestRange: 50000,
        tabs: [{id:0, text:'QTY'}, {id:1, text:'NOMINAL'}],
        selected_tab: {id:0, text:'QTY'}
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
            let prm = {}
            prm.token = one_token()
            prm.sdate = context.state.sdate
            prm.edate = context.state.edate
            prm.warehouse_id = context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0

            return context.dispatch("system/postme", {
                url: "report/one_iv_010/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_items", d)
                    return d
                        // context.commit("set_total_item", d.total)
                }
            }, { root: true })
        },

        async searchNominal(context) {
            let prm = {}
            prm.token = one_token()
            prm.sdate = context.state.sdate2
            prm.edate = context.state.edate2

            context.dispatch("system/postme", {
                url: "report/one_iv_010/search_nominal",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['items2', d])
                        // context.commit("set_total_item", d.total)
                }
            }, { root: true })
        },

        async search_warehouse(context) {
            let prm = {
                search: '',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/warehouse/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_warehouses", d.records)
                    return d.records
                }
            }, { root: true })
        }
    }
}