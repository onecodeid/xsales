// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, lastmonth_date, current_date } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,
        save: false,
        edit: false,
        dialog_new: false,
        current_date: current_date(),

        memo_id: 0,
        memo_number: 'XxX/234/2002',
        memo_note: 'Catatan memo',
        memo_memo: '',
        memo_amount: 800000,
        memo_used: 0,
        memo_refunded: 0,
        invoice_number: 'YyY/234/3003',
        invoice_id: 0,

        memo_date: current_date(),

        memos: [],
        selected_memo: null,

        customers: [],
        selected_customer: null,

        vendors: [],
        selected_vendor: null,

        accounts: [],
        selected_account: null,

        total_memo: 0,
        s_date: lastmonth_date(),
        e_date: current_date(),
        current_page: 1,
        total_memo_page: 1,
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
        async save(context) {
            context.commit('set_common', ['save', true])

            let prm = {
                hdata: JSON.stringify({
                    memo_date: context.state.memo_date,
                    // memo_customer: context.state.selected_customer.customer_id,
                    memo_vendor: context.state.selected_vendor.vendor_id,
                    memo_account: context.state.selected_account ? context.state.selected_account.account_id : 0,
                    memo_amount: context.state.memo_amount,
                    memo_note: context.state.memo_note,
                    memo_tags: context.rootState.tag.selected_tagnames ? JSON.stringify(context.rootState.tag.selected_tagnames) : "[]"
                })
            }
            if (context.state.edit) prm.memo_id = context.state.memo_id

            return context.dispatch("system/postme", {
                url: "finance/memodebit/save",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['save', false])
                },
                failback: function(e) {

                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date
            }

            context.dispatch("system/postme", {
                url: "finance/memodebit/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["memos", d.records])
                    context.commit("set_object", ["total_memo", d.total])
                    context.commit("set_object", ["total_memo_page", d.total_page])
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                memo_id: context.state.memo_id
            }

            return context.dispatch("system/postme", {
                url: "finance/memodebit/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_memo", d])
                    return d
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d.records])
                }
            }, { root: true })
        },

        async search_account_memo(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["memo_accounts", d.records])
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

        async search_customer(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/customer/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['customers', d.records])

                    return d
                }
            }, { root: true })

            return x
        },

        async search_vendor(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/vendor/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['vendors', d.records])

                    return d
                }
            }, { root: true })

            return x
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d.records])
                }
            }, { root: true })
        }
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