// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_item.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_item: {},
        dialog_new: false,
        dialog_alias: false,
        dialog_pack: false,
        dialog_stock: false,
        dialog_assembly: false,
        edit_alias: false,
        edit_pack: false,

        item_name: "",
        item_alias: "",
        item_code: "",
        item_address: "",
        item_weight: 0,
        item_img: '',
        item_min: 0,
        item_hpp: 0,
        item_hpp_edit: false,
        item_price: 0,
        item_alias: '',

        units: [],
        selected_unit: {},
        total_unit: 0,

        packs: [],
        selected_pack: {},
        view_in_packs: [{ id: "N", text: "Dalam SATUAN" }, { id: "Y", text: "Dalam KEMASAN" }],
        selected_view_in_pack: null,

        categories: [],
        selected_category: {},
        total_category: 0,

        aliases: [],
        selected_alias: {},

        itempacks: [],
        selected_itempack: null,
        selected_itempack_pack: null,

        item_assembly: "N",
        item_assembly_qty: 1,
        item_assembly_note: "",
        assemblies: [],
        assemblies_tmp: [],
        assembly_default: { item: null, qty: 0, hpp: 0, note: "" },

        assembly_accounts: [],
        assembly_costs: [],
        assembly_costs_tmp: [],
        assembly_cost_default: { account: null, amount: 0 },

        items: [],

        customers: [],
        selected_customer: null,

        stocks: [],
        selected_stock: null,

        discs: [],
        selected_disc: null,

        fees: []
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

        set_selected_item(state, val) {
            state.selected_item = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_dialog_new(state, v) {
            state.dialog_new = v
        },

        set_units(state, v) {
            state.units = v
        },

        set_selected_unit(state, v) {
            state.selected_unit = v
        },

        set_categories(state, v) {
            state.categories = v
        },

        set_selected_category(state, v) {
            state.selected_category = v
        },

        set_aliases(state, v) {
            state.aliases = v
        },

        set_selected_alias(state, v) {
            state.selected_alias = v
        },

        set_itempacks(state, v) {
            state.itempacks = v
        },

        set_selected_itempack(state, v) {
            state.selected_itempack = v
        },

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
        },

        set_stocks(state, val) {
            state.stocks = val
        },

        set_selected_stock(state, val) {
            state.selected_stock = val
        },

        set_packs(state, val) {
            state.packs = val
        },

        set_selected_pack(state, val) {
            state.selected_pack = val
        },

        set_selected_view_in_pack(state, val) {
            state.selected_view_in_pack = val
        },

        set_selected_itempack_pack(state, val) {
            state.selected_itempack_pack = val
        }
    },
    actions: {
        async save(context) {

            try {
                let aliases = []
                let packs = []
                let stockmins = []
                let costs = []
                let assemblies = []
                for (let al of context.state.aliases)
                    aliases.push({
                        customer: al.customer_id,
                        name: al.item_alias
                    })

                for (let al of context.state.itempacks)
                    packs.push({
                        customer: al.customer_id,
                        pack: al.pack_id
                    })

                for (let stock of context.state.stocks) {
                    stockmins.push({
                        warehouse: stock.warehouse_id,
                        qty: stock.stock_min
                    })
                }

                if (context.state.item_assembly == "Y") {
                    for (let cost of context.state.assembly_costs)
                        costs.push({ account: cost.account.account_id, amount: cost.amount, account_name: cost.account.account_name })

                    for (let assembly of context.state.assemblies) {
                        if (!!assembly.item && parseFloat(assembly.qty) > 0)
                            assemblies.push({ itemid: assembly.item.item_id, qty: assembly.qty, hpp: assembly.hpp, note: assembly.note })
                    }

                }

                let prm = {
                    token: one_token(),
                    hdata: {
                        name: context.state.item_name,
                        alias: context.state.item_alias,
                        code: context.state.item_code,

                        unit: context.state.selected_unit.unit_id,
                        pack: context.state.selected_pack ? context.state.selected_pack.pack_id : 0,
                        category: context.state.selected_category.category_id,
                        viewinpack: context.state.selected_view_in_pack.id,

                        disc: context.state.selected_disc ? context.state.selected_disc.disc_id : 0,

                        min_stock: context.state.item_min,
                        hpp: context.state.item_hpp,
                        price: context.state.item_price,

                        assembly: context.state.item_assembly,
                        assembly_qty: context.state.item_assembly_qty,
                        assembly_note: context.state.item_assembly_note,
                        assemblies: assemblies,
                        assembly_costs: costs
                    },
                    aliases: JSON.stringify(aliases),
                    packs: JSON.stringify(packs),
                    stockmins: JSON.stringify(stockmins)
                }

                if (context.state.edit) {
                    prm.item_id = context.rootState.item.selected_item.item_id
                    if (!!context.state.item_hpp_edit)
                        prm.hdata.hpp_edit = "Y"
                }
                prm.hdata = JSON.stringify(prm.hdata)

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_common", ["dialog_new", false])
                    context.dispatch("item/search", {}, { root: true })
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_unit(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                prm.search = ''
                let resp = await api.search_unit(prm)

                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total
                    }

                    context.commit("set_units", data.records)
                    context.commit("set_common", ["total_unit", data.total])

                    if (!!context.state.selected_unit)
                        for (let u of data.records)
                            if (u.unit_id == context.state.selected_unit.unit_id)
                                context.commit('set_selected_unit', u)
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_category(context, prm) {
            context.commit("update_search_status", 1)
            try {
                prm.token = one_token()
                let resp = await api.search_category(prm)

                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    let data = {
                        records: resp.data.records,
                        total: resp.data.total
                    }

                    context.commit("set_categories", data.records)
                    context.commit("set_common", ["total_category", data.total])

                    if (!!context.state.selected_category)
                        for (let u of data.records)
                            if (u.category_id == context.state.selected_category.category_id)
                                context.commit('set_selected_category', u)
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_customer(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/customer/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_customers", d.records)
                }
            }, { root: true })
        },

        async search_alias(context) {
            let prm = {
                search: '',
                page: 1,
                item: context.rootState.item.selected_item.item_id
            }

            context.dispatch("system/postme", {
                url: "master/itemalias/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_aliases", d.records)
                }
            }, { root: true })
        },

        async search_itempack(context) {
            let prm = {
                search: '',
                page: 1,
                item: context.rootState.item.selected_item.item_id
            }

            context.dispatch("system/postme", {
                url: "master/itempack/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_itempacks", d.records)
                }
            }, { root: true })
        },

        async search_stock(context) {
            let prm = {
                item_id: context.rootState.item.selected_item.item_id
            }

            context.dispatch("system/postme", {
                url: "inventory/stock/search_by_item",
                prm: prm,
                callback: function(d) {
                    context.commit("set_stocks", d)
                }
            }, { root: true })
        },

        async search_pack(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/pack/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_packs", d.records)

                    if (!!context.state.selected_pack)
                        for (let u of d.records)
                            if (u.pack_id == context.state.selected_pack.pack_id)
                                context.commit('set_selected_pack', u)
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/item/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['items', d.records])
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: '',
                page: 1,
                limit: 100,
                side: 'P'
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['assembly_accounts', d.records])
                }
            }, { root: true })
        },

        async search_disc(context) {
            let prm = {
                search: '',
                page: 1,
                limit: 100
            }

            context.dispatch("system/postme", {
                url: "master/disc/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['discs', d.records])
                }
            }, { root: true })
        }
    }
}