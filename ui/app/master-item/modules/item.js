// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_item.js"
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

        current_page: 1,

        dialog_stock_card: false,
        dialog_history: false,
        dialog_history_detail: false,

        histories: [],
        selected_history: null,
        history_data: {},

        sdate: new Date().toISOString().substr(0, 10),
        edate: new Date().toISOString().substr(0, 10),

        assemblies: [{id:'Y', text:'ITEM RAKITAN'}, {id:'N', text:'ITEM NON RAKITAN'}],
        selected_assembly: null
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
        async search(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                prm.search = context.state.search
                prm.page = context.state.current_page
                if (context.state.selected_assembly)
                    prm.assembly = context.state.selected_assembly.id
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

                    context.commit("update_items", data.records)
                    context.commit("update_total_item", data.total)
                    context.commit("update_total_item_page", data.total_page)
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async del(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = { token: one_token(), id: context.state.selected_item.item_id }


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

        async search_history(context) {
            let prm = {
                search: '',
                page: 1,
                limit: 100,
                item_id: context.state.selected_item.item_id
            }

            context.dispatch("system/postme", {
                url: "inventory/stock/histories",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_object", ['histories', d.records])
                }
            }, { root: true })
        },

        async search_history_detail(context) {
            let prm = {
                log_id: context.state.selected_history.log_id,
                log_code: context.state.selected_history.log_code
            }

            context.dispatch("system/postme", {
                url: "inventory/stock/history_detail",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_object", ['history_data', d])
                }
            }, { root: true })
        },

        async setSlow(context, data) {
            let prm = {
                token: one_token(),
                item_id: context.state.selected_item.item_id,
                warehouse_id: data.warehouse_id,
                value: data.value
            }

            let rst = context.dispatch("system/postme", {
                url: "master/item/set_slow",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })

            return rst
        }
    }
}