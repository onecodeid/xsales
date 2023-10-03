// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, current_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,

        accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: null,

        receive_no: '',
        receive_from: '',
        receive_note: '',
        receive_memo: '',
        receive_amount: '',
        receive_tax: 'N',
        receive_img: '',

        taxes: [],
        selected_tax: null,

        tags: [],
        selected_tags: null,

        receive_date: current_date()
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

        update_search_status(state, val) {
            state.search_status = val
        }
    },
    actions: {
        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/account/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d.records])
                }
            }, { root: true })
        },

        async search_tax(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/tax/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["taxes", d.records])
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
        },
        // async search(context) {
        //     context.commit("update_search_status", 1)

        //     let prm = {}
        //     prm.token = one_token()
        //     prm.date = context.state.edate

        //     context.dispatch("system/postme", {
        //         url: "analytic/sales/mtd_projo",
        //         prm: prm,
        //         callback: function(d) {
        //             context.commit("set_sales", d.records)
        //             context.commit("set_total_sales", d.total)
        //         }
        //     }, { root: true })
        // }
    }
}