// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_system.js"
import * as api2 from "./api_common.min.js?t=as"
import { URL, one_token, one_user, page_sizes } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search: '',
        search_status: 0,
        search_error_message: "",
        menu_level_0: [],
        menu_level_1: [],
        menu_level_2: [],
        total_menu: 0,
        //sipe add breadcrumb dan hak akses
        bread_crumb: "",
        is_page_allowed: true,
        dashboard: "",
        user: one_user(),
        one_token: one_token(),
        page_sizes: page_sizes(),

        menus: [],
        icons: [],
        drawer: false,

        notif_unread: 0,
        notif_total: 0,
        notif_messages: [],
        drawer_notif: false,
        wscon: null,
        notif_md5: '',
        audio: null,
        // sound: 'http://soundbible.com/mp3/Elevator Ding-SoundBible.com-685385892.mp3',
        sound: '',
        notif_muted: true,
        conf: {}
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

        update_bread_crumb(state, val) {
            state.bread_crumb = val
        },
        update_dashboard(state, val) {
            state.dashboard = val
        },
        update_page_allowed(state, val) {
            state.is_page_allowed = val
        },
        update_search(state, val) {
            state.search = val
        },
        update_search_error_message(state, status) {
            state.search_error_message = status
        },
        update_search_status(state, status) {
            state.search_status = status
        },

        update_menu_level_0(state, data) {
            state.menu_level_0 = data
        },

        update_menu_level_1(state, data) {
            state.menu_level_1 = data
        },

        update_menu_level_2(state, data) {
            state.menu_level_2 = data
        },

        set_menus(state, v) {
            state.menus = v
        },

        set_icons(state, v) {
            state.icons = v
        },

        set_drawer(state, v) {
            state.drawer = v
        },

        set_drawer_notif(state, v) {
            state.drawer_notif = v
        },

        set_notif_unread(state, v) {
            state.notif_unread = v
        },

        set_notif_total(state, v) {
            state.notif_total = v
        },

        set_notif_messages(state, v) {
            state.notif_messages = v
        },

        set_notif_md5(state, v) {
            state.notif_md5 = v
        },

        set_wscon(state, v) {
            state.wscon = v
        },

        set_audio(state, v) {
            state.audio = v
        },

        set_notif_muted(state, v) {
            state.notif_muted = v
        },

        set_conf(state, v) {
            state.conf = v
        }
    },
    actions: {
        async search_menu_group(context) {
            context.commit("update_search_status", 1)
            try {
                let resp = await api.search_menu_group({ token: one_token() })
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    context.commit('set_menus', resp.data[0])
                    context.commit('set_icons', resp.data[1])
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
            }
        },

        async logout() {
            try {
                var resp = await axios.post(URL + 'systm/user/logout', { token: one_token() });
                if (resp.status != 200) {
                    return {
                        status: "ERR",
                        message: resp.statusText
                    };
                }
                let data = resp.data;
                return data;
            } catch (e) {
                return {
                    status: "ERR",
                    message: e.message
                };
            }
        },

        async do_logout(context) {
            let x = async() => {
                try {
                    let resp = await context.dispatch('logout')
                    if (resp.status != "OK") {
                        alert('error')
                    } else {
                        window.localStorage.removeItem("token")
                        window.localStorage.removeItem("user")
                        location.replace('../system-login/')
                    }
                } catch (e) {
                    context.commit("update_login_status", 3)
                    context.commit("update_login_error_message", e.message)
                }
            }

            x()
        },

        async get_notif_unread(context) {
            return
            context.commit("update_search_status", 1)
            try {
                let resp = await api.get_notif_unread({ token: one_token() })
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    if (resp.data.md5 != context.state.notif_md5) {
                        context.commit('set_notif_unread', resp.data.unread)
                        context.commit('set_notif_total', resp.data.total)
                        context.commit('set_notif_messages', resp.data.messages)
                        context.commit('set_notif_md5', resp.data.md5)

                        // let audio = context.state.audio
                        // if (audio)
                        //     audio.play();
                    }
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
            }
        },

        async set_notif_read(context) {
            context.commit("update_search_status", 1)
            try {
                let resp = await api.set_notif_read({ token: one_token() })
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    context.dispatch('get_notif_unread')
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
            }
        },

        async postme(context, d) {
            let aurl = d.url
            let prm = d.prm
            let hdr = d.hdr
            let callback = d.callback
            let failback = d.failback

            context.commit("set_common", ['search_status', 1])
            try {
                prm.token = context.state.one_token
                let resp = await api2.postme(aurl, prm, hdr)

                if (resp.status != "OK") {
                    context.dispatch('postme_fail', resp)
                    if (failback)
                        failback(resp.message)

                    return resp
                } else {
                    context.dispatch('postme_success')
                    if (callback)
                        callback(resp.data)

                    return resp.data
                }
            } catch (e) {
                context.dispatch('postme_fail', e)
                console.log(e)
                if (failback)
                    failback(e.message)
            }
        },

        async report_excel(context, prm) {
            context.commit("set_common", ['search_status', 1])

            try {
                prm.token = one_token()
                let resp = await api2.report_excel(prm.url, prm)

                if (resp.status != "OK") {
                    context.dispatch('postme_fail', resp)
                } else {
                    context.dispatch('postme_success')

                    window.open(resp.data.report_url)
                    context.commit("set_dialog_print", false, { root: true })
                }

            } catch (e) {
                context.dispatch('postme_fail', e)
            }
        },

        async get_conf(context) {

            return context.dispatch("system/postme", {
                prm: {},
                url: "systm/conf/get_conf",
                callback: function(d) {
                    context.commit('set_conf', d)
                    return d
                }
            }, { root: true })
        },

        postme_success(context) {
            context.commit("set_common", ['search_status', 2])
            context.commit("set_common", ['search_error_message', ""])
        },

        postme_fail(context, e) {
            context.commit("set_common", ['search_status', 3])
            context.commit("set_common", ['search_error_message', e.message])
        }
    }
}