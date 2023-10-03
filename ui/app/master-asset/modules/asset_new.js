// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_asset.js"
import { one_token, current_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',
        
        assets: [],
        total_asset: 0,

        asset_id: 0,
        asset_name: 0,
        asset_code: '',
        asset_acq_cost: 0,
        asset_acq_account: null,
        asset_description: '',
        asset_depreciable: 'N',
        asset_dep_time: 1,
        asset_dep_rate: 100,
        asset_dep_account: null,
        asset_dep_accumulated_account: null,
        asset_dep_accumulated_amount: 0,

        accounts: [],
        selected_account: null,

        methods: [],
        selected_method: null,

        acq_date: current_date(),

        dialog_new: false
    },
    mutations: {
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                if (Array.isArray(name))
                    for(let n of name) eval(`state.${n} = "${val}"`)
                else
                    eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
        },

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_accounts (state, val) {
            state.accounts = val
        },

        set_selected_account (state, val) {
            state.selected_account = val
        },

        set_dep_account (state, val) {
            state.asset_dep_account = val
        },

        set_dep_accumulated_account (state, val) {
            state.asset_dep_accumulated_account = val
        },

        set_acq_account (state, val) {
            state.asset_acq_account = val
        },

        set_methods (state, val) {
            state.methods = val
        },

        set_selected_method (state, val) {
            state.selected_method = val
        }
    },
    actions: {
        async search_account(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/account/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, {root:true})
        },

        async search_method(context) {
            let prm = {
                search : '',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/depmethod/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_methods", d.records)
                }
            }, {root:true})
        },

        async save(context) {
            let prm = {
                asset_name: context.state.asset_name,
                    asset_code: context.state.asset_code,
                    asset_description: context.state.asset_description,
                    asset_account_id: context.state.selected_account.account_id,
                    asset_acq_date: context.state.acq_date,
                    asset_acq_cost: context.state.asset_acq_cost,
                    asset_acq_account: context.state.asset_acq_account.account_id,
                    asset_depreciable: context.state.asset_depreciable,
                    asset_dep_method: context.state.selected_method.depmethod_id,
                    asset_dep_time: context.state.asset_dep_time,
                    asset_dep_rate: context.state.asset_dep_rate,
                    asset_dep_account: context.state.asset_dep_account.account_id,
                    asset_dep_accumulated_account: context.state.asset_dep_accumulated_account.account_id,
                    asset_dep_value: context.state.asset_dep_value
                }
            if (context.state.edit)
                prm.asset_id = context.state.asset_id

            context.dispatch("system/postme", {
                url:"master/asset/save",
                prm:prm,
                callback:function(d) {
                    context.commit("set_common", ['dialog_new', false])
                    context.dispatch("asset/search", null, {root:true})
                }
            }, {root:true})
        }

                    
        // async save(context, fn) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = {}
        //         prm.token = one_token()
        //         prm.asset_name = context.state.asset_name
        //         prm.asset_code = context.state.asset_code

        //         if (context.state.edit)
        //             prm.asset_id = context.rootState.asset.selected_asset.M_AssetID

        //         let resp = await api.save(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")

        //             if (fn) {
        //                 fn()
        //             } else
        //                 context.dispatch('asset/search', {}, {root:true})
        //             context.commit('set_common', ['dialog_new', false])
        //         }
        //         return resp
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //     }
        // }
    }
}