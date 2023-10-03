// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_invoice.js"
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
        
        invoices: [],
        total_invoice: 0,
        
        invoice_id: 0,
        invoice_note: '',
        invoice_number: '',
        invoice_ppn: 'Y',
        invoice_date: current_date(),
        invoice_due_date: current_date(),
        invoice_due_date_check: "Y",
        invoice_disc: 0,
        invoice_discrp: 0,
        invoice_disctype: 'R',
        invoice_dps: [],
        invoice_dp_default: {dp:null, amount:0},
        invoice_dp: 0,
        invoice_shipping: 0,
        invoice_proforma: 'N',

        customers: [],
        selected_customer: null,

        warehouses: [],
        selected_warehouse: null,

        items: [],
        selected_item: null,

        terms: [],
        selected_term: null,

        details: [],
        detail_default: {delivery:null, items:[]},

        dps: [],

        dialog_new: false,
        dialog_proforma: false
    },
    mutations: {
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
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

        set_invoice_note(state, val) {
            state.invoice_note = val
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

        set_terms(state, val) {
            state.terms = val
        },

        set_selected_term(state, val) {
            state.selected_term = val
        },

        set_dps(state, val) {
            state.dps = val
        },

        set_invoice_dps(state, val) {
            state.invoice_dps = val
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
                search : '',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/warehouse/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_warehouses", d.records)
                }
            }, {root:true})
        },

        async search_term(context) {
            let prm = {
                search : '',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/term/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_terms", d.records)
                }
            }, {root:true})
        },

        async search_customer(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/customer/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_customers", d.records)
                }
            }, {root:true})
        },

        async search_item(context) {
            let prm = {
                search : '%',
                page : 1,
                customer_id : context.state.selected_customer.customer_id,
                invoice_only: true
            }

            let url = "sales/delivery/search_dd"
            if (context.state.invoice_proforma == 'Y') {
                url = "sales/sales/search_4_proforma"

                if (context.rootState.sales)
                    prm.sales_id = context.rootState.sales.selected_sales.sales_id
            }
                
            context.dispatch("system/postme", {
                url:url,
                prm:prm,
                callback:function(d) {
                    context.commit("set_items", d)

                    if (context.state.invoice_proforma == 'Y' &&
                        !context.state.edit) {
                            let dx = []
                            let dfl = JSON.parse(JSON.stringify(context.state.detail_default))
                            for (let i in d) {
                                dx.push(dfl)
                                dx[i].delivery = d[i]
                                dx[i].items = []
                                if (d[i])
                                    if (d[i].items)
                                        dx[i].items = d[i].items
                            }
                            context.commit('set_details', dx)
                    }
                }
            }, {root:true})
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if (!!d.delivery)
                    ds.push(d.delivery.delivery_id)

            let dps = []
            for (let d of context.state.invoice_dps)
                if (!!d.dp && d.amount > 0)
                    dps.push({id:d.dp.dp_id, amount:d.amount})
                    
            let hdata = {
                date:context.state.invoice_date,
                due_date:context.state.invoice_due_date,
                note: context.state.invoice_note,
                customer: context.state.selected_customer.customer_id,
                disc: context.state.invoice_disc,
                discrp: context.state.invoice_discrp,
                shipping: context.state.invoice_shipping,
                dp: context.state.invoice_dp,
                term: context.state.selected_term.term_id
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds),
                dps: JSON.stringify(dps)
            }
            if (context.state.edit) prm.invoice_id = context.state.invoice_id

            context.dispatch("system/postme", {
                url:"sales/invoice/save",
                prm:prm,
                callback:function(d) {
                    context.dispatch('invoice/search', null, {root:true})
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, {root:true})
        },

        async search_dp(context) {
            let prm = {
                search : context.state.search,
                page : 1,
                full: 'N',
                customer: context.state.selected_customer.customer_id
            }

            if (!!context.state.edit) {
                let edits = []
                for (let d of context.state.invoice_dps)
                    edits.push(d.dp.dp_id)
                prm.edits = edits.join(',')
            }

            context.dispatch("system/postme", {
                url:"finance/paymentdp/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_dps", d.records)
                }
            }, {root:true})
        },

        async save_proforma(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            for (let d of context.state.details)
                if (!!d.delivery)
                    ds.push(d.delivery.sales_id)

            let dps = []
            for (let d of context.state.invoice_dps)
                if (!!d.dp && d.amount > 0)
                    dps.push({id:d.dp.dp_id, amount:d.amount})
                    
            let hdata = {
                date:context.state.invoice_date,
                due_date:context.state.invoice_due_date,
                note: context.state.invoice_note,
                customer: context.state.selected_customer.customer_id,
                disc: context.state.invoice_disc,
                discrp: context.state.invoice_discrp,
                shipping: context.state.invoice_shipping,
                dp: context.state.invoice_dp,
                term: context.state.selected_term.term_id
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds),
                dps: JSON.stringify(dps)
            }
            if (context.state.edit) prm.invoice_id = context.state.invoice_id

            context.dispatch("system/postme", {
                url:"sales/invoice/save_proforma",
                prm:prm,
                callback:function(d) {
                    if (context.rootState.invoice)
                        context.dispatch('invoice/search', null, {root:true})
                    if (context.rootState.sales)
                        context.dispatch('sales/search', null, {root:true})
                    
                    context.commit('set_common', ['dialog_proforma', false])
                    context.commit('set_common', ['save', false])

                    if (context.rootState.sales) {
                        // console.log(d)
                        // this.select(x)
                        // let so = x
                        context.commit('set_report_url', context.rootState.invoice.URL+"report/one_sales_002?id="+d.invoice_id, {root:true})
                        context.commit('set_dialog_print', true, {root:true})
                        // this.$store.commit('set_dialog_print', true)

                        // context.commit('')
                    }
                }
            }, {root:true})
        }
    }
}