// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_cash.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: null,

        journaltypes: [],
        selected_journaltype: null,
        payable_info: {},

        paymentdetails: [],
        selected_paymentdetails: null,

        tags: [],
        selected_tags: null,

        tagnames: [],
        selected_tagnames: [],

        snackbar: false,
        snackbar_text: '',

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

        update_accounts(state, data) {
            state.accounts = data
        },

        set_selected_account(state, val) {
            state.selected_account = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_account(state, val) {
            state.total_account = val
        },

        update_total_account_page(state, val) {
            state.total_account_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_journaltypes(state, val) {
            state.journaltypes = val
        },

        set_selected_journaltype(state, val) {
            state.selected_journaltype = val
        },

        set_payable_info(state, val) {
            state.payable_info = val
        },

        set_paymentdetails(state, val) {
            state.paymentdetails = val
        },

        set_selected_paymentdetail(state, val) {
            state.selected_paymentdetail = val
        }
    },
    actions: {
        async search(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.search = context.state.search
                prm.page = context.state.current_page
                prm.group_id = 1
                prm.limit = 1000
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

                    context.commit("update_accounts", data.records)
                    context.commit("update_total_account", data.total)
                    context.commit("update_total_account_page", data.total_page)

                    if (window.getParameter("selected")) {
                        for (let d of data.records) {
                            if (d.M_AccountID == window.getParameter("selected")) {
                                context.commit("set_selected_account", d)
                                context.commit('set_selected_tab', 2, { root: true })
                                context.dispatch('journal_detail/search', null, { root: true })

                                window.history.pushState({}, document.title, window.location.href.replace(/\?[a-z\=0-9]+/, ''));
                            }
                        }
                    }
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async del(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.id = context.state.selected_account.M_AccountID

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
        },

        async search_journaltype(context) {
            let prm = {
                search: 'J.1',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/journaltype/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_journaltypes", d.records)
                }
            }, { root: true })
        },

        async search_payable_info(context) {
            let prm = {}

            context.dispatch("system/postme", {
                url: "finance/misc/get_payable_info",
                prm: prm,
                callback: function(d) {
                    context.commit('set_payable_info', d)
                }
            }, { root: true })
        },

        async search_paymentdetail(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/paymentdetail/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_paymentdetails", d)
                }
            }, { root: true })
        },

        async search_tag(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/tag/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['tags', d.records])

                    let tags = []
                    for (let r of d.records) tags.push(r.tag_name)
                    context.commit("set_object", ['tagnames', tags])
                }
            }, { root: true })
        }
    }
}