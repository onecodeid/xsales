// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',

        groups: [],
        selected_group: {},

        accountreports: [],
        accounts: [],
        accountgroups: [],
        tmp: [],

        current_page: 1,
        snackbar: false,
        snackbar_text: 'Snackbar Text'
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
            state[v[0]] = v[1]
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
        }
    },
    actions: {
        async searchGroup(context) {
            let prm = {
                token: one_token()
            }

            return context.dispatch("system/postme", {
                url: "master/accountreport/search_group",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['groups', d])

                    return d
                }
            }, { root: true })
        },

        async search(context) {
            let prm = {
                token: one_token(),
                group: context.state.selected_group.id
            }

            return context.dispatch("system/postme", {
                url: "master/accountreport/search",
                prm: prm,
                callback: function(d) {

                    // modify
                    // let datas = []
                    // let tmp = []
                    // for (let x of d) {
                    //     if (datas.length == 0 || tmp.indexOf(x.account_type) < 0) {
                    //         datas.push({ account_type: x.account_type, account_title: x.account_title, details: [] })
                    //         tmp.push(x.account_type)
                    //     }


                    //     datas[datas.length - 1].details.push(x)
                    // }

                    context.commit("set_object", ['accountreports', d])
                    return d
                }
            }, { root: true })
        },

        async searchAccount(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                limit: 1000,
                token: one_token()
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {

                    let tmp = []
                    for (let i in d.records) {
                        d.records[i].is_group = 'N'
                        tmp.push(d.records[i].account_code)
                    }

                    // d.records.unshift({ header: 'AKUN / REKENING' })
                    context.commit("set_object", ["accounts", d.records])
                    context.commit("set_object", ["tmp", tmp])

                    return d.records
                }
            }, { root: true })
        },

        async searchAccountGroup(context) {
            let prm = {
                limit: 1000,
                search: ''
            }

            return context.dispatch("system/postme", {
                url: "master/accountgroup/search",
                prm: prm,
                callback: function(d) {
                    let tmp = context.state.tmp
                    let accs = []

                    // let z = [{ header: 'GRUP AKUN' }]
                    for (let g of d.records) {
                        tmp.push(g.group_code)
                    }
                    tmp = tmp.sort()

                    for (let t of tmp) {
                        for (let a of context.state.accounts) {
                            if (a.account_code == t)
                                accs.push(a)
                        }

                        console.log(t)
                        for (let g of d.records) {

                            if (g.group_code == t) {
                                accs.push({ account_id: g.group_id, account_name: 'GRUP : ' + g.group_name, account_code: g.group_code, is_group: 'Y' })
                            }

                        }
                    }

                    // z.push({ account_id: g.group_id, account_name: 'GRUP : ' + g.group_name, account_code: g.group_code, is_group: 'Y' })
                    // d.records.unshift({ header: 'GRUP AKUN' })
                    // let x = JSON.parse(JSON.stringify(context.state.accounts))
                    // let y = z.concat(x)
                    context.commit('set_object', ['accounts', accs])
                    return accs
                }
            }, { root: true })
        },

        async save(context) {
            let jdatas = []
            let n = 0;
            for (let ar of context.state.accountreports)
                for (let ard of ar.details) {
                    ard.sort = ++n;
                    jdatas.push(ard)
                }

            let prm = {
                token: one_token(),
                group: context.state.selected_group.id,
                jdata: JSON.stringify(jdatas)
            }

            return context.dispatch("system/postme", {
                url: "master/accountreport/save",
                prm: prm,
                callback: function(d) {
                    this.search()
                    return d
                }
            }, { root: true })
        }



        // async save(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         prm.token = one_token()
        //         prm.id = context.state.selected_account.M_AccountID

        //         let resp = await api.del(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)

        //             context.commit("set_common", ["snackbar_text", resp.message])
        //             context.commit("set_object", ["snackbar", true])
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")

        //             context.dispatch('search', {})
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // }
    }
}