// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "../../assets/js/api_common.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        token: one_token(),
        search_status: 0,
        search_error_message: '',
        search: '',
        
        assets: [],
        total_asset: 0,
        total_asset_page: 0,
        selected_asset: {},

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

        update_search(state, search) {
            state.search = search
        },

        update_assets(state, data) {
            state.assets = data
        },

        set_selected_asset(state, val) {
            state.selected_asset = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_asset(state, val) {
            state.total_asset = val
        },

        update_total_asset_page(state, val) {
            state.total_asset_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                search : context.state.search,
                page : context.state.current_page
            }

            context.dispatch("system/postme", {
                url:"master/asset/search",
                prm:prm,
                callback:function(d) {
                    context.commit("update_assets", d.records)
                    context.commit("update_total_asset", d.total)
                    context.commit("update_total_asset_page", d.total_page)
                }
            }, {root:true})
        },

        async del(context) {
            let prm = { id: context.state.selected_asset.asset_id }
            context.dispatch("system/postme", {
                url:"master/asset/del",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        }
    }
}