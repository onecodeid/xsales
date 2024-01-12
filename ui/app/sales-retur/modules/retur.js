// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, current_date, lastmonth_date } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,
        dialog_invoice: false,
        edit: false,

        customers: [],
        selected_customer: null,

        warehouses: [],
        selected_warehouse: null,
        // accounts: [],
        // total_account: 0,
        // total_account_page: 0,
        // selected_account: null,
        // selected_account_to: null,

        // taxes: [],
        // selected_tax: null,
        retur_id: 0,
        retur_number: '',
        retur_date: current_date(),
        retur_note: '',

        items: [],
        selectedItem: null,

        // Listing
        returs: [],
        total_retur: 0,
        selected_retur: null,

        memo_id: 0,
        memo_used: 0,
        memo_refunded: 0,

        s_date: lastmonth_date(),
        e_date: current_date(),
        current_page: 1,
        total_retur_page: 1,

        invoices: [],
        selectedInvoice: null,

        details: []
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

        update_search_status(state, val) {
            state.search_status = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0
            }

            context.dispatch("system/postme", {
                url: "sales/retur/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["returs", d.records])
                    context.commit("set_object", ["total_retur", d.total])
                    context.commit("set_object", ["total_retur_page", d.total_page])
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                retur_id: context.state.retur_id
            }

            return context.dispatch("system/postme", {
                url: "sales/retur/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_retur", d])
                    return d
                }
            }, { root: true })
        },

        async save(context) {
            let hdata = {
                date: context.state.retur_date,
                customer: context.state.selected_customer.customer_id,
                invoice: context.rootState.invoice.selected_invoice.sales_id,
                warehouse: context.state.selected_warehouse.warehouse_id,
                note: context.state.retur_note
            }

            let items = []
            let subtotal = 0,
                ppn = "N",
                ppn_amount = 0,
                ppn_value = 0,
                total = 0
            for (let item of context.state.details) {
                items.push({
                    invoice: item.detail_id,
                    item: item.item_id,
                    qty: item.retur_qty,
                    note: item.retur_note,
                    price: item.price,
                    disc: item.disc
                })

                subtotal += parseFloat(item.price * item.retur_qty * (100-item.disc) / 100)
                ppn_amount += 0
                total += subtotal
                ppn = "N"
                ppn_value = 0
            }

            hdata.subtotal = subtotal
            hdata.ppn_amount = ppn_amount
            hdata.ppn = ppn
            hdata.ppn_value = ppn_value
            hdata.total = total

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(items)
            }
            if (context.state.edit)
                prm.retur_id = context.state.retur_id

            return context.dispatch("system/postme", {
                url: "sales/retur/save",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                }
            }, { root: true })
        },


        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/account/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d.records])
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
                    context.commit("set_object", ['customers', d.records])
                    // if (!!context.state.selected_customer) {
                    //     for (let c of d.records)
                    //         if (c.customer_id == context.state.selected_customer.customer_id)
                    //             context.commit('set_selected_customer', c)
                    // }

                    return d
                }
            }, { root: true })

            return x
        },

        async search_invoice(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: 1,
                sdate: "2022-01-01",
                edate: current_date()
            }

            context.dispatch("system/postme", {
                url: "sales/invoice/search",
                prm: prm,
                callback: function(d) {
                    context.commit("update_invoices", d.records)
                    context.commit("update_total_invoice", d.total)
                    context.commit("update_total_invoice_page", d.total_page)
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
                    context.commit("set_object", ["warehouses", d.records])
                    context.commit("set_object", ["selected_warehouse", d.records[0]])
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/item/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["items", d.records])
                    return d.records
                }
            }, { root: true })
        },

        async searchInvoiceByItem(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0,
                item_id: context.state.selectedItem.item_id
            }

            context.dispatch("system/postme", {
                url: "sales/sales/search_available_by_item",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["invoices", d.records])
                }
            }, { root: true })
        }
    }
}