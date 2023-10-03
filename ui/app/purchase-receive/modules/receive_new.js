// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_receive.js"
import { one_token, current_date } from "../../assets/js/global.js"

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

        receives: [],
        total_receive: 0,

        receive_id: 0,
        receive_note: '',
        receive_memo: '',
        receive_number: '',
        receive_ref_number: '',
        receive_ppn: 'Y',
        receive_date: current_date(),

        vendors: [],
        selected_vendor: null,

        warehouses: [],
        selected_warehouse: null,

        items: [],
        selected_item: null,

        details: [],
        detail_default: { item: null, qty: 0, note: '' },

        dialog_new: false,
        dialog_purchase: false,
        dialog_warehouse: false,
        save_n_confirm: false
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

        set_receive_note(state, val) {
            state.receive_note = val
        },

        set_vendors(state, val) {
            state.vendors = val
        },

        set_selected_vendor(state, val) {
            state.selected_vendor = val
        },

        set_warehouses(state, val) {
            state.warehouses = val
        },

        set_selected_warehouse(state, val) {
            state.selected_warehouse = val
        }
    },
    actions: {
        async search_id(context) {
            let prm = {
                token: one_token(),
                receive_id: context.state.receive_id
            }

            let rst = context.dispatch("system/postme", {
                url: "purchase/receive/search_id",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })

            return rst
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

        async search_vendor(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/vendor/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_vendors", d.records)
                    return d
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: '%',
                page: 1,
                // vendor_id : context.state.selected_vendor.vendor_id
            }

            if (context.rootState.purchase.selected_purchase)
                prm.purchase_id = context.rootState.purchase.selected_purchase.purchase_id

            context.dispatch("system/postme", {
                url: "purchase/purchasedetail/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_items", d.records)

                    if (!context.state.edit && context.state.selected_warehouse && context.state.selected_vendor) {
                        let dfl

                        let dx = []
                        for (let i in d.records) {
                            dfl = JSON.parse(JSON.stringify(context.state.detail_default))
                            dx.push(dfl)
                            dx[i].item = d.records[i]
                            dx[i].qty = d.records[i].detail_qty
                        }
                        context.commit('set_details', dx)
                    }
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if ((d.qty > 0) && !!d.item)
                    ds.push({ itemid: d.item.item_id, purchaseid: d.item.detail_id, qty: d.qty, note: d.note })

            let hdata = {
                p_date: context.state.receive_date,
                p_total: 0,
                p_note: context.state.receive_note,
                p_memo: context.state.receive_memo,
                p_vendor: context.state.selected_vendor.vendor_id,
                p_warehouse: context.state.selected_warehouse.warehouse_id,
                p_ref_number: context.state.receive_ref_number
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.receive_id = context.state.receive_id

            let rst = context.dispatch("system/postme", {
                url: "purchase/receive/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('receive/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])

                    if (context.state.save_n_confirm) {
                        let data = JSON.parse(d)
                        context.commit('receive/set_selected_receive', data, { root: true })
                        context.dispatch('receive/confirm', null, { root: true })
                    }
                }
            }, { root: true })

            return rst
        }
    }
}