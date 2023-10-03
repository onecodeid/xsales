// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_log.js"
import { one_token, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',
        dialog_detail: false,

        logs: [],
        total_log: 0,
        total_log_page: 0,
        selected_log: {},

        items: [],
        total_item: 0,
        total_item_page: 0,
        selected_item: null,

        selected_warehouse: null,

        s_date: lastmonth_date(),
        e_date: current_date(),

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

        set_object(state, v) {
            let name = v[0]
            let val = v[1]
            state[name] = val
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
                warehouse_id: context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_iv_005/search",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_object", ["items", d.records])
                    context.commit("set_object", ["total_item", d.total])
                    context.commit("set_object", ["total_item_page", d.total_page])
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.state.s_date,
                "edate=" + context.state.e_date,
                "warehouse_id=" + (context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0)
            ];
            return prm.join("&")
        }
    }
}