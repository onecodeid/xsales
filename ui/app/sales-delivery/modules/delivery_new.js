// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_delivery.js"
import { one_token, current_date, HOST } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        current_date: current_date(),
        edit: false,
        save: false,
        view: false,
        single: false,
        HOST: HOST,
        sa: false,

        deliverys: [],
        total_delivery: 0,

        delivery_id: 0,
        delivery_note: '',
        delivery_memo: '',
        delivery_number: '',
        delivery_ref_number: '',
        delivery_ppn: 'Y',
        delivery_date: current_date(),
        delivery_send_note: '',
        delivery_proforma: 'N',

        customers: [],
        selected_customer: null,

        staffs: [],
        selected_staff: null,

        warehouses: [],
        selected_warehouse: null,

        items: [],
        selected_item: null,
        custom_item_name: '',

        details: [],
        detail_default: { item: null, qty: 0, note: '' },

        deliverytypes: [],
        selected_deliverytype: null,

        proformas: [],
        selected_proforma: null,
        total_proforma: 0,
        total_proforma_page: 1,

        dialog_new: false,
        dialog_proforma: false,
        dialog_sales_new: false,
        dialog_itemname: false,
        save_n_confirm: false,
        snackbar: false,
        snackbar_text: '',

        addresses: [],
        selected_address: null,

        expeditions: [],
        selected_expedition: null,
        expedition_name: '',
        expedition_mode: 'E',

        saless: [],
        selected_sales: null,
        tmp: [],

        invoices: []
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

        update_search_status(state, val) {
            state.search_status = val
        },

        set_details(state, val) {
            state.details = val
        },

        set_items(state, val) {
            state.items = val
        },

        set_delivery_note(state, val) {
            state.delivery_note = val
        },

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
        },

        set_warehouses(state, val) {
            state.warehouses = val
        },

        set_selected_warehouse(state, val) {
            state.selected_warehouse = val
        },

        set_deliverytypes(state, val) {
            state.deliverytypes = val
        },

        set_selected_deliverytype(state, val) {
            state.selected_deliverytype = val
        },

        set_staffs(state, val) {
            state.staffs = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
        },

        set_proformas(state, val) {
            state.proformas = val
        },

        set_selected_proforma(state, val) {
            state.selected_proforma = val
        },

        set_addresses(state, val) {
            state.addresses = val
        },

        set_selected_address(state, val) {
            state.selected_address = val
        },

        set_saless(state, data) {
            state.saless = data
        },

        set_selected_sales(state, val) {
            state.selected_sales = val
        },

        set_tmp(state, val) {
            state.tmp = val
        },

        set_expeditions(state, val) {
            state.expeditions = val
        },

        set_selected_expedition(state, val) {
            state.selected_expedition = val
        }
    },
    actions: {
        async search_id(context) {
            let prm = {
                token: one_token(),
                delivery_id: context.state.delivery_id
            }

            return context.dispatch("system/postme", {
                url: "sales/delivery/search_id",
                prm: prm,
                callback: function(d) {
                    // context.commit("update_deliverys", d.records)
                    // context.commit("update_total_delivery", d.total)
                    // context.commit("update_total_delivery_page", d.total_page)
                    return d
                }
            }, { root: true })
        },

        async search_warehouse(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/warehouse/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_warehouses", d.records)
                }
            }, { root: true })
        },

        async search_customer(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/customer/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_customers", d.records)

                    console.log(context.state.tmp)
                    if (context.state.tmp.selected_customer)
                        for (let c of d.records)
                            if (c.customer_id == context.state.tmp.selected_customer.customer_id)
                                context.commit('set_selected_customer', c)
                }
            }, { root: true })

            return x
        },

        async search_staff(context) {
            let prm = {
                search: '',
                position: 'POS.TRANSPORTER',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "systm/staff/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_staffs", d.records)
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: '%',
                page: 1,
                customer_id: context.state.selected_customer.customer_id
            }

            if (!!context.state.single &&
                !context.state.edit &&
                context.rootState.sales.selected_sales)
                prm.sales_id = context.rootState.sales.selected_sales.sales_id

            context.dispatch("system/postme", {
                url: "sales/salesdetail/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_items", d.records)

                    if (!context.state.edit && context.state.selected_warehouse && context.state.selected_customer) {
                        let dfl

                        let dx = []
                        for (let i in d.records) {
                            dfl = JSON.parse(JSON.stringify(context.state.detail_default))
                            dx.push(dfl)
                            dx[i].item = d.records[i]
                            dx[i].qty = d.records[i].detail_qty
                        }
                        context.commit('set_details', dx)

                        // CHECK STOK
                        context.dispatch('search_stock')
                    }
                }
            }, { root: true })
        },

        async search_deliverytype(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/deliverytype/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_deliverytypes", d.records)
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if ((d.qty > 0) && !!d.item) {
                    let item = { itemid: d.item.item_id, salesid: d.item.detail_id, qty: d.qty, note: d.note }
                    if (d.item.custom_name)
                        item.itemname = d.item.item_name
                    ds.push(item)
                }

            let hdata = {
                p_date: context.state.delivery_date,
                p_total: 0,
                p_note: context.state.delivery_note,
                p_memo: context.state.delivery_memo,
                // p_customer: context.state.selected_customer.customer_id,
                p_type: context.state.selected_deliverytype.deliverytype_id,
                p_send_note: context.state.delivery_send_note,
                p_staff: context.state.selected_staff ? context.state.selected_staff.staff_id : 0,
                p_warehouse: context.state.selected_warehouse.warehouse_id,
                p_ref_number: context.state.delivery_ref_number,
                p_proforma: context.state.delivery_proforma,
                p_exp: context.state.selected_expedition ? context.state.selected_expedition.expedition_id : 0
            }
            if (context.state.delivery_proforma == 'Y')
                hdata.p_customer = context.state.selected_proforma.customer_id
            else
                hdata.p_customer = context.state.selected_customer.customer_id

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds),
                proforma: context.state.delivery_proforma
            }
            if (context.state.edit) prm.delivery_id = context.state.delivery_id
            if (context.state.delivery_proforma == 'Y') prm.delivery_id = context.state.selected_proforma.delivery_id

            context.dispatch("system/postme", {
                url: "sales/delivery/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('delivery/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['dialog_proforma', false])
                    context.commit('set_common', ['save', false])

                    if (context.state.save_n_confirm) {
                        let data = JSON.parse(d)
                        context.commit('delivery/set_selected_delivery', data, { root: true })
                        context.dispatch('delivery/confirm', null, { root: true })
                    }
                },
                failback: function(e) {
                    context.commit('set_common', ['snackbar_text', e])
                    context.commit('set_common', ['snackbar', true])
                }
            }, { root: true })
        },

        async search_proforma(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page
            }

            context.dispatch("system/postme", {
                url: "sales/delivery/search_proforma",
                prm: prm,
                callback: function(d) {
                    context.commit("set_proformas", d.records)
                    context.commit('set_common', ['total_proforma', d.total])
                    context.commit('set_common', ['total_proforma_page', d.total_page])
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
                            if (item.item_id == details[i].item.item_id) {
                                // alert('a')
                                details[i].item.stock = item.stock_qty
                            }

                        }
                    }
                    context.commit('set_details', details)

                    // context.commit("set_proformas", d.records)
                }
            }, { root: true })
        },

        async search_address(context) {
            let prm = {
                search: '',
                page: 1,
                customer: context.state.selected_customer.customer_id
            }

            context.dispatch("system/postme", {
                url: "master/deliveryaddress/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_addresses", d.records)

                    if (context.state.edit) {
                        for (let add of d.records)
                            if (add.address_id == context.rootState.delivery.selected_delivery.address_id)
                                context.commit('set_selected_address', add)
                    }

                    if (context.state.tmp_address_id != 0) {
                        for (let add of d.records)
                            if (add.address_id == context.state.tmp_address_id)
                                context.commit('set_selected_address', add)
                        context.commit('set_common', ['tmp_address_id', 0])
                    }
                }
            }, { root: true })
        },

        async search_sales(context) {
            let prm = {
                token: one_token(),
                search: '',
                page: 1,
                sdate: "1971-01-01",
                edate: context.state.current_date,
                staff: 0,
                done: 'N',
                customer: context.state.selected_customer.customer_id
            }

            context.commit('set_selected_sales', null)
            context.dispatch("system/postme", {
                url: "sales/sales/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_saless", d.records)
                }
            }, { root: true })
        },

        async save_item_name(context) {
            let prm = {
                token: one_token(),
                id: context.state.selected_item.item.detail_id, // deliverydetail_id,
                itemname: context.state.custom_item_name
            }

            let x = context.dispatch("system/postme", {
                url: "sales/salesdetail/save_item_name",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })

            return x
        }
    }
}