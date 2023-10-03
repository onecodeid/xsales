// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_staff.js"
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        staffs: [],
        total_staff: 0,
        total_staff_page: 0,
        selected_staff: {},

        pos_code: null,
        positions: [],
        selected_position: null,

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
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_staffs(state, data) {
            state.staffs = data
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_staff(state, val) {
            state.total_staff = val
        },

        update_total_staff_page(state, val) {
            state.total_staff_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context) {
            let prm = {}
            prm.token = one_token()
            prm.search = context.state.search
            prm.page = context.state.current_page
            prm.position = context.state.pos_code

            context.dispatch("system/postme", {
                url: "systm/staff/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['staffs', d.records])
                    context.commit("set_object", ['total_staff', d.total])
                    context.commit("set_object", ['total_staff_page', d.total_page])
                }
            }, { root: true })
        },

        async search_driver(context) {
            return context.dispatch('search')
        },

        async search_position(context) {
            let prm = {}
            prm.token = one_token()
            prm.search = context.state.pos_code
            prm.page = 1

            context.dispatch("system/postme", {
                url: "systm/position/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['positions', d.records])
                }
            }, { root: true })
        },

        // async search(context, prm) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         prm.token = one_token()
        //         prm.search = context.state.search
        //         prm.page = context.state.current_page
        //         let resp = await api.search(prm)

        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")
        //             let data = {
        //                 records: resp.data.records,
        //                 total: resp.data.total,
        //                 total_page: resp.data.total_page
        //             }

        //             context.commit("update_staffs", data.records)
        //             context.commit("update_total_staff", data.total)
        //             context.commit("update_total_staff_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = { token: one_token(), id: context.state.selected_staff.staff_id }


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