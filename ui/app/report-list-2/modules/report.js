// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_report.js?t=12ssd3"
import { URL, one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        groups: [],
        selected_group: null,
        selected_report: null,

        URL: URL
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

        set_groups(state, data) {
            state.groups = data
        },

        set_selected_group(state, val) {
            state.selected_group = val
        },

        set_selected_report(state, val) {
            state.selected_report = val
        }
    },
    actions: {
        async search_groups(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token()
                }

                let resp = await api.search_groups(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_groups", resp.data.records)
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        }
    }
}