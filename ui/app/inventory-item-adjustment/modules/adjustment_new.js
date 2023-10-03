// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_adjustment.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_adjustment: {},
        dialog_new: false,
        dialog_item: false,

        adjustment_id: 0,
        adjustment_number: "",
        adjustment_date: "",
        adjustment_note: "",

        items: [],
        total_item: 0,
        selected_item: {},
        item_search: '',

        warehouses: [],
        selected_warehouse: null,

        items_av: [],
        total_item_av: 0,
        selected_item_av: {}
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

        set_selected_adjustment(state, val) {
            state.selected_adjustment = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_dialog_new(state, v) {
            state.dialog_new = v
        },

        set_items(state, v) {
            state.items = v
        },

        set_selected_item(state, v) {
            state.selected_item = v
        },

        set_items_av(state, v) {
            state.items_av = v
        },

        set_selected_item_av(state, v) {
            state.selected_item_av = v
        },

        set_warehouses(state, v) {
            state.warehouses = v
        },

        set_selected_warehouse(state, v) {
            state.selected_warehouse = v
        }
    },
    actions: {
        async search_id(context) {
            let prm = {
                token: one_token(),
                adjust_id: context.state.adjustment_id
            }

            let rst = context.dispatch("system/postme", {
                url: "inventory/adjust/search_id",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })

            return rst
        },

        async save(context) {

            try {
                let prm = {
                    token: one_token(),
                    jdata: JSON.stringify(context.state.items),
                    adjust_id: 0,
                    hdata: JSON.stringify({
                        adjust_note: context.state.adjustment_note,
                        warehouse: context.state.selected_warehouse.warehouse_id
                    })
                }

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                        // let data = {
                        //     records: resp.data.records,
                        //     total: resp.data.total
                        // }

                    context.commit("set_common", ["dialog_new", false])
                    context.dispatch("adjustment/search", {}, { root: true })
                        // context.commit("set_cities", data.records)
                        // context.commit("update_common", data.total)
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_detail(context) {
            context.commit("set_common", ["search_status", 1])
            try {

                let prm = {
                    token: one_token(),
                    adjust_id: context.state.edit ? context.rootState.adjustment.selected_adjustment.I_AdjustID : 0
                }
                let resp = await api.search_detail(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total
                    }

                    context.commit("set_items", data.records)
                    context.commit("set_common", ['item_total', data.total])
                }
            } catch (e) {
                context.commit("set_common", ["search_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_item(context) {
            context.commit("set_common", ["search_status", 1])
            try {
                let prm = { token: one_token(), warehouse: context.state.selected_warehouse.warehouse_id, search: context.state.item_search }
                let resp = await api.search_item(prm)

                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total
                    }

                    context.commit("set_items_av", data.records)
                    context.commit("set_common", ['total_item_av', data.total])
                }
            } catch (e) {
                context.commit("set_common", ["search_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_warehouse(context) {
            let prm = {
                search: '',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/warehouse/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_warehouses", d.records)
                    return d
                }
            }, { root: true })
        }
    }
}