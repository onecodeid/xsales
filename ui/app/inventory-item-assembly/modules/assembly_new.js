// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_assembly.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',
        view: false,

        selected_assembly: {},
        dialog_new: false,
        dialog_item: false,

        assembly_id: 0,
        assembly_number: "",
        assembly_date: "",
        assembly_note: "",

        items: [],
        total_item: 0,
        selected_out_item: null,
        out_item_qty: 0,

        items_master: [],

        warehouses: [],
        selected_warehouse: null,

        items_av: [],
        total_item_av: 0,
        selected_item_av: {},

        details: [],
        detail_default: { item: null, qty: 0, hpp: 0, stock: 0 },

        accounts: [],
        costs: [],
        cost_default: { account: null, amount: 0 },

        error: false,
        error_msg: ''
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

        set_selected_assembly(state, val) {
            state.selected_assembly = val
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

        set_selected_out_item(state, v) {
            state.selected_out_item = v
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
        },

        set_details(state, v) {
            state.details = v
        },

        set_accounts(state, data) {
            state.accounts = data
        },

        set_costs(state, data) {
            state.costs = data
        }
    },
    actions: {
        async search_id(context) {
            let prm = {
                token: one_token(),
                assembly_id: context.state.assembly_id
            }

            let rst = context.dispatch("system/postme", {
                url: "inventory/assembly/search_id",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })

            return rst
        },

        async save(context) {

            try {
                let ds = []
                let costs = []
                for (let dts of context.state.details)
                    ds.push({ item_id: dts.item.item_id, item_qty: dts.qty, item_hpp: dts.item.item_hpp })

                for (let cst of context.state.costs)
                    costs.push({
                        account: { account_id: cst.account.account_id, account_name: cst.account.account_name },
                        amount: cst.amount
                    })

                let prm = {
                    token: one_token(),
                    jdata: JSON.stringify(ds),
                    assembly_id: 0,
                    hdata: JSON.stringify({
                        assembly_date: context.state.assembly_date,
                        assembly_note: context.state.assembly_note,
                        warehouse: context.state.selected_warehouse.warehouse_id,
                        to_item_id: context.state.selected_out_item.item_id,
                        to_item_qty: context.state.out_item_qty
                    }),
                    accdata: JSON.stringify(costs)
                }

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)

                    context.commit("set_common", ["error", true])
                    context.commit("set_common", ["error_msg", resp.message.message])
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                        // let data = {
                        //     records: resp.data.records,
                        //     total: resp.data.total
                        // }

                    context.commit("set_common", ["dialog_new", false])
                    context.dispatch("assembly/search", {}, { root: true })


                    // context.commit("set_cities", data.records)
                    // context.commit("update_common", data.total)
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_detail(context) {
            context.commit("set_common", ["search_status", 1])
            try {

                let prm = {
                    token: one_token(),
                    assembly_id: context.state.edit ? context.rootState.assembly.selected_assembly.I_AssemblyID : 0
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

        async search_item_master(context) {
            let prm = {
                token: one_token(),
                // warehouse: context.state.selected_warehouse.warehouse_id,
                assembly: 'Y'
            }

            context.dispatch("system/postme", {
                url: "master/item/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['items_master', d.records])
                }
            }, { root: true })
        },

        async search_item(context) {


            context.commit("set_common", ["search_status", 1])
            try {

                let prm = { token: one_token(), warehouse: context.state.selected_warehouse.warehouse_id }
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
        },

        async search_account(context) {
            let prm = {
                search: '',
                page: 1,
                side: 'P',
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_accounts", d.records)
                }
            }, { root: true })
        },

        async search_stock(context) {
            let items = []
            for (let d of context.state.details)
                items.push(d.item.item_id)

            if (items.length < 1)
                return

            let prm = {
                token: one_token(),
                item_ids: items,
                warehouse_id: context.state.selected_warehouse.warehouse_id
            }

            context.dispatch("system/postme", {
                url: "inventory/stock/search_by_items",
                prm: prm,
                callback: function(d) {
                    let details = JSON.parse(JSON.stringify(context.state.details))

                    for (let i in details) {

                        for (let item of d) {
                            console.log(item)
                            if (item.item_id == details[i].item.item_id) {
                                details[i].item.stock = item.stock_qty
                                details[i].stock = item.stock_qty
                            }
                        }

                    }
                    // console.log(details)
                    context.commit('set_details', details)

                    // context.commit("set_proformas", d.records)
                }
            }, { root: true })
        }
    }
}