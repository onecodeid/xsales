// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_purchase.js"
import { one_token, current_date, PPN } from "../../assets/js/global.js"

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
        ppn: PPN,

        purchases: [],
        total_purchase: 0,

        purchase_id: 0,
        purchase_note: '',
        purchase_memo: '',
        purchase_number: '',
        purchase_ppn: 'N',
        purchase_ppn_amount: 'N',
        purchase_date: current_date(),
        purchase_disc: 0,
        purchase_discrp: 0,
        purchase_disctype: 'R',
        purchase_shipping: 0,
        purchase_grandtotal: 0,
        purchase_dp: 0,

        vendors: [],
        selected_vendor: null,

        staffs: [],
        selected_staff: null,

        paymentplans: [],
        selected_paymentplan: null,

        terms: [],
        selected_term: null,

        items: [],
        selected_item: null,

        details: [],
        detail_default: { item: null, qty: 0, price: 0, disc: 0, discrp: 0, ppn: 'N', ppn_amount: 0, subtotal: 0, itemtotal: 0, total: 0, disctype: 'P' },

        dialog_new: false
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

        set_purchase_note(state, val) {
            state.purchase_note = val
        },

        set_vendors(state, val) {
            state.vendors = val
        },

        set_selected_vendor(state, val) {
            state.selected_vendor = val
        },

        set_staffs(state, val) {
            state.staffs = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
        },

        set_paymentplans(state, val) {
            state.paymentplans = val
        },

        set_selected_paymentplan(state, val) {
            state.selected_paymentplan = val
        }
    },
    actions: {
        async search_vendor(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            let x = context.dispatch("system/postme", {
                url: "master/vendor/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_vendors", d.records)
                }
            }, { root: true })

            return x
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
                    context.commit("set_items", d.records)
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            let disc, discrp
            for (let d of context.state.details)
                if ((d.qty > 0) && !!d.item) {
                    disc = d.disctype == 'P' ? d.disc : 0
                    discrp = d.disctype == 'R' ? d.discrp : 0
                    ds.push({ itemid: d.item.item_id, price: d.price, qty: d.qty, disc: disc, discrp: discrp, ppn: d.ppn, subtotal: d.total })
                }

            let hdata = {
                p_date: context.state.purchase_date,
                p_total: 0,
                p_ppn: context.state.purchase_ppn,
                p_disc: context.state.purchase_disc,
                p_discrp: context.state.purchase_discrp,
                p_shipping: context.state.purchase_shipping,
                p_dp: context.state.purchase_dp,
                p_note: context.state.purchase_note,
                p_memo: context.state.purchase_memo,
                p_vendor: context.state.selected_vendor.vendor_id,
                p_staff: context.state.selected_staff.staff_id,
                // p_payment: context.state.selected_paymentplan.paymentplan_id,
                p_term: context.state.selected_term.term_id
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.purchase_id = context.state.purchase_id

            context.dispatch("system/postme", {
                url: "purchase/purchase/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('purchase/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        async search_staff(context) {
            let prm = {
                search: '',
                position: 'POS.ADMIN',
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

        async search_paymentplan(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/paymentplan/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_paymentplans", d.records)
                }
            }, { root: true })
        },

        async search_term(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/term/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['terms', d.records])
                }
            }, { root: true })
        }
    }
}