// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_bill.js"
import { one_token, veryStartDate, current_date, lastmonth_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        bills: [],
        total_bill: 0,
        total_bill_page: 0,
        selected_bill: {},

        s_date: veryStartDate,
        e_date: current_date(),
        due_date: current_date(),

        accounts: [],
        cash_accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: null,
        tmp_acc_id: 0,
        tmp_pay_id: 0,

        headerStats: [
            { title: 'Pembelian Belum Terbayar', amount: 0, color: 'cyan' },
            { title: 'Pembelian Jatuh Tempo', amount: 0, color: 'orange', url: 'finance-bill/due' },
            { title: 'Pelunasan 30 Hari Terakhir', amount: 0, color: 'green' }
        ],

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

        update_bills(state, data) {
            state.bills = data
        },

        set_selected_bill(state, val) {
            state.selected_bill = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_bill(state, val) {
            state.total_bill = val
        },

        update_total_bill_page(state, val) {
            state.total_bill_page = val
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

            context.dispatch("system/postme", {
                url: "finance/bill/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_bills", d.records)
                    context.commit("update_total_bill", d.total)
                    context.commit("update_total_bill_page", d.total_page)
                }
            }, { root: true })
        },

        async search_due(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                duedate: context.state.due_date,
                lunas: 'N'
            }

            context.dispatch("system/postme", {
                url: "finance/bill/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_bills", d.records)
                    context.commit("update_total_bill", d.total)
                    context.commit("update_total_bill_page", d.total_page)
                }
            }, { root: true })
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

        //             context.commit("update_bills", data.records)
        //             context.commit("update_total_bill", data.total)
        //             context.commit("update_total_bill_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                bill_id: context.state.selected_bill.bill_id
            }

            context.dispatch("system/postme", {
                url: "finance/bill/delete",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async post(context) {
            let prm = {
                id: context.state.selected_bill.bill_id
            }

            context.dispatch("system/postme", {
                url: "trans/bill/post",
                prm: prm,
                callback: function(d) {
                    context.dispatch("search")
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: "",
                page: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let accs = []
                    for (let acc of d.records) {
                        if (acc.parent == "0") acc.parent = false
                        accs.push(acc)
                    }

                    context.commit("set_object", ["accounts", accs])
                }
            }, { root: true })
        },

        async search_account_cash(context) {
            let prm = {
                search: "",
                page: 1,
                group_id: 1,
                limit: 1000
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let accs = []
                    for (let acc of d.records) {
                        if (acc.parent == "0") acc.parent = false
                        accs.push(acc)
                    }

                    context.commit("set_object", ["cash_accounts", accs])
                }
            }, { root: true })
        },

        async searchHeaderStats(context) {
            let prm = {
                token: one_token()
            }

            context.dispatch("system/postme", {
                url: "finance/bill/header_stats",
                prm: prm,
                callback: function(d) {
                    let x = JSON.parse(JSON.stringify(context.state.headerStats))
                    x[0].amount = d.total_unpaid
                    x[1].amount = d.total_due
                    x[2].amount = d.total_payment
                    context.commit('set_object', ['headerStats', x])
                }
            }, { root: true })
        },
    }
}