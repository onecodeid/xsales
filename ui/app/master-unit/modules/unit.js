// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_unit.js"
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',
        
        units: [],
        total_unit: 0,
        total_unit_page: 0,
        selected_unit: {},

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

        update_units(state, data) {
            state.units = data
        },

        set_selected_unit(state, val) {
            state.selected_unit = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_unit(state, val) {
            state.total_unit = val
        },

        update_total_unit_page(state, val) {
            state.total_unit_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.search = context.state.search
                prm.page = context.state.current_page
                let resp = await api.search(prm)
                
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total,
                        total_page: resp.data.total_page
                    }

                    context.commit("update_units", data.records)
                    context.commit("update_total_unit", data.total)
                    context.commit("update_total_unit_page", data.total_page)
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async del(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {token : one_token(), id : context.state.selected_unit.unit_id}


                let resp = await api.del(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    
                    context.dispatch('search', {})
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}