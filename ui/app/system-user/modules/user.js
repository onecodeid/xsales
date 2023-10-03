// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_user.js?t=123"
import { URL, one_token, one_user } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        groups: [],
        selected_group: null,

        users: [],
        selected_user: null,
        total_user: 0,

        menus: [],
        privileges: [],
        reports: [],
        report_privileges: [],

        dialog_group: false,
        dialog_user: false,
        snackbar: false,
        snackbar_text: '',

        URL: URL,
        one_user: one_user()
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

        set_users(state, data) {
            state.users = data
        },

        set_selected_user(state, val) {
            state.selected_user = val
        },

        set_menus(state, data) {
            state.menus = data
        },

        set_reports(state, data) {
            state.reports = data
        },

        set_privileges(state, data) {
            state.privileges = data
        },

        set_report_privileges(state, data) {
            state.report_privileges = data
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
        },

        async search_menus(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token()
                }

                let resp = await api.search_menus(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_menus", resp.data)
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_reports(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token()
                }

                let resp = await api.search_reports(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_reports", resp.data.records)
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async save(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token(),
                    group_id: context.state.selected_group.group_id,
                    privileges: context.state.privileges,
                    report_privileges: context.state.report_privileges,
                }

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', 'User Group telah diupdate'])
                    context.commit('set_common', ['dialog_group', false])
                    context.dispatch('search_groups')
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_users(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token(),
                    group_id: context.state.selected_group.group_id
                }

                let resp = await api.search_users(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_users", resp.data.records)
                    context.commit("set_common", ['total_user', resp.data.total])
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async del(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token(),
                    id: context.state.selected_user.user_id
                }

                let resp = await api.del(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")
                    context.dispatch('search_users')
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        }
    }
}