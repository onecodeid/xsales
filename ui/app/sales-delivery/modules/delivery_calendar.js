// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_delivery.js"
import { one_token, current_date, lastmonth_date, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        cal_date: current_date(),
        cal_items: []
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

        set_cal_items(state, v) {
            state.cal_items = v
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                cal_date: context.state.cal_date
            }

            context.dispatch("system/postme", {
                url: "sales/sales/search_month",
                prm: prm,
                callback: function(d) {
                    // console.log(d.records)
                    let data = {}
                    for (let r of d.records) {
                        r.sales_datex = r.sales_date.split('-').reverse().join('-')
                        if (!data[r.sales_datex])
                            data[r.sales_datex] = [r]
                        else
                            data[r.sales_datex].push(r)
                    }

                    context.commit("set_cal_items", data)
                }
            }, { root: true })
        }
    }
}