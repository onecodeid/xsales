// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_user.js"
import { URL, one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        user_name: '',
        user_full_name: '',
        user_address: '',
        user_phone: '',
        user_email: '',
        user_join: new Date().toISOString().substr(0, 10),
        user_pass: '',
        old_pass: '',

        sexs: [],
        selected_sex: null,

        snackbar: false,
        snackbar_text: 'User telah diupdate',

        URL: URL,
        dialog_user: false,
        user_check: false,
        error_check: true
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
        }
    },
    actions: {
        async save(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token(),
                    group_id: context.rootState.user.selected_group.group_id,
                    group_name: context.rootState.user.selected_group.group_name,
                    user_name: context.state.user_name,
                    user_full_name: context.state.user_full_name,
                    user_address: context.state.user_address,
                    user_phone: context.state.user_phone,
                    user_email: context.state.user_email,
                    user_join: context.state.user_join,
                    user_pass: context.state.user_pass,
                    old_pass: context.state.old_pass,
                    user_id: context.state.edit ? context.rootState.user.selected_user.user_id : 0
                }

                let resp = await api.save_user(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")

                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', 'User telah diupdate'])
                    context.commit('set_common', ['dialog_user', false])
                    context.dispatch('user/search_users', null, {root:true})
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async check_user(context) {
            context.commit("set_common", ['search_status', 1])
            context.commit('set_common', ['user_check', true])
            try {
                let prm = {
                    token: one_token(),
                    user_name: context.state.user_name
                }

                let resp = await api.check_user(prm)
                context.commit('set_common', ['user_check', false])
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_status', 3])
                    context.commit("update_search_error_message", resp.message)
                    context.commit("set_common", ["error_check", true])
                } else {
                    context.commit("set_common", ['search_status', 2])
                    context.commit("update_search_error_message", "")
                    context.commit("set_common", ["error_check", false])
                }
            } catch (e) {
                context.commit("set_common", ['search_status', 3])
                context.commit("update_search_error_message", e.message)
                context.commit('set_common', ['user_check', false])
            }
        }
    }
}