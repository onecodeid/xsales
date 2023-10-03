// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_item.js"
import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        items: [],
        total_item: 0,
        total_item_page: 0,
        selected_item: {},

        xitems: [],
        selected_xitem: null,

        current_page: 1,
        unmap: 'N'
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

        update_items(state, data) {
            state.items = data
        },

        set_selected_item(state, val) {
            state.selected_item = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_item(state, val) {
            state.total_item = val
        },

        update_total_item_page(state, val) {
            state.total_item_page = val
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
                unmap: context.state.unmap
            }

            context.dispatch("system/postme", {
                url: "tmp/tmp_sales_import_item/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["items", d.records])
                    context.commit("set_object", ["total_item", d.total])
                    context.commit("set_object", ["total_item_page", d.total_page])
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/item/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["xitems", d.records])
                }
            }, { root: true })

            return x
        },

        async save(context) {
            let prm = {
                id: context.state.selected_item.id,
                b: context.state.selected_xitem.item_name,
                c: context.state.selected_xitem.item_id
            }

            let x = context.dispatch("system/postme", {
                url: "tmp/tmp_sales_import_item/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('search')
                }
            }, { root: true })

            return x
        },

        // async del(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = { token: one_token(), id: context.state.selected_item.item_id }


        //         let resp = await api.del(prm)
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
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