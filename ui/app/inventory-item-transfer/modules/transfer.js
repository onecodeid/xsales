// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_transfer.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',

        transfers: [],
        total_transfer: 0,
        selected_transfer: {},

        selected_warehouse: null,
        selected_to_warehouse: null,

        current_page: 1,
        current_date: new Date().toISOString().substr(0, 10),
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

        update_transfers(state, data) {
            state.transfers = data
        },

        set_selected_transfer(state, val) {
            state.selected_transfer = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_transfer(state, val) {
            state.total_transfer = val
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

        set_selected_warehouse(state, v) {
            state.selected_warehouse = v
        },

        set_selected_to_warehouse(state, v) {
            state.selected_to_warehouse = v
        }
    },
    actions: {
        async search(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.sdate = context.state.sdate
                prm.edate = context.state.edate
                prm.search = context.state.query
                prm.from = context.state.selected_warehouse ? context.state.selected_warehouse.warehouse_id : 0
                prm.to = context.state.selected_to_warehouse ? context.state.selected_to_warehouse.warehouse_id : 0

                let resp = await api.search(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total
                    }

                    context.commit("update_transfers", data.records)
                    context.commit("update_total_transfer", data.total)
                }

                return resp.data
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async del(context, prm) {
            context.commit("update_search_status", 1)
            try {
                console.log(prm)
                prm.token = one_token()
                let resp = await api.del(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    context.dispatch('search', [])
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}