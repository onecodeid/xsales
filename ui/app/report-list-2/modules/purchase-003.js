// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_bill.js"
import { one_token, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        bills: [],
        total_bill: 0,
        total_bill_page: 0,
        selected_bill: {},

        s_date: lastmonth_date(),
        e_date: current_date(),
        selected_vendor: null,

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
                vendor_id: context.state.selected_vendor ? context.state.selected_vendor.vendor_id : 0
            }

            context.dispatch("system/postme", {
                url: "report/one_purchase_003/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["bills", d.records])
                    context.commit("set_object", ["total_bill", d.total])
                    context.commit("set_object", ["total_bill_page", d.total_page])
                }
            }, { root: true })
        },

        async collect(context) {
            let prm = [
                "token=" + one_token(),
                "search=" + context.state.search,
                "sdate=" + context.state.s_date,
                "edate=" + context.state.e_date,
                "vendor_id=" + (context.state.selected_vendor ? context.state.selected_vendor.vendor_id : 0)
            ];
            return prm.join("&")
        }
    }
}