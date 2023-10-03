// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_login.js"
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        username: '',
        password: '',

        error: false,
        error_message: ''
    },
    mutations: {
        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_username (state, v) {
            state.username = v
        },

        set_password (state, v) {
            state.password = v
        },

        set_error (state, v) {
            state.error = v
        },

        set_error_message (state, v) {
            state.error_message = v
        }
    },
    actions: {
        async login(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {
                    username: context.state.username,
                    password: context.state.password
                }

                let resp = await api.login(prm)
                
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)

                    context.commit('set_error', true)
                    context.commit('set_error_message', resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    let data = {
                        token: resp.data.token,
                        user: resp.data.user
                    }

                    localStorage.setItem("token", data.token)
                    localStorage.setItem('user', JSON.stringify(data.user))
                    
                    window.location = URL + "../ui/app/"+data.user.dashboard;
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
            }
        }
    }
}