// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_bill.js"
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

        bills: [],
        total_bill: 0,

        bill_id: 0,
        bill_note: '',
        bill_number: '',
        bill_ppn: 'Y',
        bill_date: current_date(),
        bill_due_date: current_date(),
        bill_due_date_check: "Y",
        bill_disc: 0,
        bill_discrp: 0,
        bill_disctype: 'R',
        bill_dps: [],
        bill_dp_default: { dp: null, amount: 0 },
        bill_dp: 0,

        vendors: [],
        selected_vendor: null,

        warehouses: [],
        selected_warehouse: null,

        items: [],
        selected_item: null,

        terms: [],
        selected_term: null,

        details: [],
        detail_default: { receive: null, items: [] },

        dps: [],

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

        set_bill_note(state, val) {
            state.bill_note = val
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
        },

        set_dps(state, val) {
            state.dps = val
        },

        set_bill_dps(state, val) {
            state.bill_dps = val
        }
    },
    actions: {
        // async search_account(context) {
        //     let prm = {
        //         search : context.state.search,
        //         page : 1
        //     }

        //     context.dispatch("system/postme", {
        //         url:"master/account/search_dd",
        //         prm:prm,
        //         callback:function(d) {
        //             context.commit("set_accounts", d.records)
        //         }
        //     }, {root:true})
        // },

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

        async search_vendor(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/vendor/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_vendors", d.records)
                }
            }, { root: true })
        },

        async search_item(context) {
            let prm = {
                search: '%',
                page: 1,
                vendor_id: context.state.selected_vendor.vendor_id
            }

            context.dispatch("system/postme", {
                url: "purchase/receive/search_dd",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_items", d)
                }
            }, { root: true })
        },

        async search_dp(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                full: 'N',
                customer: context.state.selected_vendor.vendor_id
            }

            if (!!context.state.edit) {
                let edits = []
                for (let d of context.state.bill_dps)
                    edits.push(d.dp.dp_id)
                prm.edits = edits.join(',')
            }

            return context.dispatch("system/postme", {
                url: "finance/billdp/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_dps", d.records)
                    return d
                }
            }, { root: true })
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if (!!d.receive)
                    ds.push(d.receive.receive_id)

            let dps = []
            for (let d of context.state.bill_dps)
                if (!!d.dp && d.amount > 0)
                    dps.push({ id: d.dp.dp_id, amount: d.amount })

            let hdata = {
                date: context.state.bill_date,
                due_date: context.state.bill_due_date,
                note: context.state.bill_note,
                vendor: context.state.selected_vendor.vendor_id,
                disc: context.state.bill_disc,
                discrp: context.state.bill_discrp,
                dp: context.state.bill_dp
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: ds[0], //JSON.stringify(ds),
                dps: JSON.stringify(dps)
            }
            if (context.state.edit) prm.bill_id = context.state.bill_id

            context.dispatch("system/postme", {
                url: "finance/bill/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('bill/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
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