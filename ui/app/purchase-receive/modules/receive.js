// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_receive.js"
import { one_token, veryStartDate, current_date, lastmonth_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        receives: [],
        total_receive: 0,
        total_receive_page: 0,
        selected_receive: {},

        s_date: veryStartDate,
        e_date: current_date(),

        current_page: 1,

        bill_note: '',
        dialog_bill: false,
        bill_save: false,

        dialog_confirm: false,
        confirm_save: false
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

        update_receives(state, data) {
            state.receives = data
        },

        set_selected_receive(state, val) {
            state.selected_receive = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_receive(state, val) {
            state.total_receive = val
        },

        update_total_receive_page(state, val) {
            state.total_receive_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date
            }

            let rst = context.dispatch("system/postme", {
                url: "purchase/receive/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_receives", d.records)
                    context.commit("update_total_receive", d.total)
                    context.commit("update_total_receive_page", d.total_page)
                }
            }, { root: true })

            return rst
        },

        // async searchx(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = {}
        //         prm.token = one_token()
        //         prm.search = context.state.search
        //         prm.page = context.state.current_page
        //         prm.sdate = context.state.s_date
        //         prm.edate = context.state.e_date
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

        //             context.commit("update_receives", data.records)
        //             context.commit("update_total_receive", data.total)
        //             context.commit("update_total_receive_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                receive_id: context.state.selected_receive.receive_id
            }

            context.dispatch("system/postme", {
                url: "purchase/receive/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async confirm(context) {
            let prm = {
                receive_id: context.state.selected_receive.receive_id
            }

            context.commit('set_common', ['confirm_save', true])
            context.dispatch("system/postme", {
                url: "purchase/receive/confirm",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['confirm_save', false])
                    context.commit('set_common', ['dialog_confirm', false])
                        // context.dispatch("search")

                    context.dispatch("bill")
                },
                failback: function(e) {
                    context.commit('set_common', ['confirm_save', false])
                }
            }, { root: true })
        },

        async bill(context) {
            let prm = {
                jdata: context.state.selected_receive.receive_id, //JSON.stringify([context.state.selected_receive.receive_id]),
                hdata: JSON.stringify({ note: context.state.selected_receive.receive_note /*context.state.bill_note*/ })
            }

            context.commit('set_common', ['bill_save', true])
            context.dispatch("system/postme", {
                url: "finance/bill/save_create",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['bill_save', false])
                    context.commit('set_common', ['dialog_bill', false])
                    context.dispatch("search")
                },
                failback: function(e) {
                    context.commit('set_common', ['bill_save', false])
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_receive.receive_id
            }

            context.dispatch("system/postme", {
                url: "trans/receive/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        }
    }
}