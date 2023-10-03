// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_invoice.js"
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
        sa: false,
        HOST: HOST,

        invoices: [],
        total_invoice: 0,
        selected_invoice: null,

        invoice_id: 0,
        invoice_note: '',
        invoice_memo: '',
        invoice_number: '',
        invoice_ppn: 'Y',
        invoice_date: current_date(),
        invoice_due_date: current_date(),
        invoice_due_date_check: "Y",
        invoice_disc: 0,
        invoice_discrp: 0,
        invoice_disctype: 'R',
        invoice_dps: [],
        invoice_dp_default: { dp: null, amount: 0 },
        invoice_dp: 0,
        invoice_shipping: 0,
        invoice_proforma: 'N',
        invoice_address: '',

        customers: [],
        selected_customer: null,

        warehouses: [],
        selected_warehouse: null,

        items: [],
        selected_item: null,

        terms: [],
        selected_term: null,

        details: [],
        detail_default: { delivery: null, items: [] },

        sales_name: '',

        dps: [],

        dialog_new: false,
        dialog_proforma: false,
        dialog_delivery: false
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
            state[v[0]] = v[1]
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
        },

        set_selected_invoice(state, val) {
            state.selected_invoice = val
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

            return context.dispatch("system/postme", {
                url: "master/warehouse/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_warehouses", d.records)
                    return d
                }
            }, { root: true })
        },

        async search_term(context) {
            let prm = {
                search: '',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/term/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_terms", d.records)
                    return d
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
                }
            }, { root: true })

            return x
        },

        async search_item(context) {
            let prm = {
                search: '%',
                page: 1,
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : 0,
                invoice_only: true
            }

            // Single
            if (!!context.rootState.delivery)
                if (!!context.rootState.delivery.selected_delivery)
                    prm.delivery_id = context.rootState.delivery.selected_delivery.delivery_id

            let url = "sales/delivery/search_dd"
            if (context.state.invoice_proforma == 'Y') {
                url = "sales/sales/search_4_proforma"

                if (context.rootState.sales)
                    prm.sales_id = context.rootState.sales.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url: url,
                prm: prm,
                callback: function(d) {
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

                    // NEW
                    if (!context.state.edit) {
                        let dx = []
                        let dpsi = [] // DP Index
                        let dps = [] // DP Data
                        let dpt = 0 // DP Total
                        let term = 0 // Term
                        let term_duration = 0 // Term Duration
                        let dfl
                        for (let i in d) {
                            dfl = JSON.parse(JSON.stringify(context.state.detail_default))
                            dx.push(dfl)
                            dx[i].delivery = d[i]
                            dx[i].items = []
                            if (d[i])
                                if (d[i].items)
                                    dx[i].items = d[i].items

                                // DP
                            if (d[i].dp_id != 0 && dpsi.indexOf(d[i].dp_id) < 0) {
                                dpsi.push(d[i].dp_id)
                                dps.push({ amount: d[i].dp_amount, dp: d[i].dp })
                                dpt += parseFloat(d[i].dp_amount)
                            }

                            if (d[i].term_id > 0) {
                                term = d[i].term_id
                                term_duration = d[i].term_duration
                            }

                        }
                        context.commit('set_details', dx)
                        context.commit('set_invoice_dps', dps) // SET DP Data
                        context.commit('set_common', ['invoice_dp', dpt])

                        // SET Term
                        if (term != 0)
                            for (let t of context.state.terms)
                                if (t.term_id == term) {
                                    context.commit('set_selected_term', t)
                                    let due_date = moment(context.state.current_date, "YYYY-MM-DD")
                                        .add(Math.round(term_duration), 'days').format('DD-MM-YYYY')
                                    context.commit('set_common', ['invoice_due_date', due_date])
                                }

                    }

                }
            }, { root: true })
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
                    dps.push({ id: d.dp.dp_id, amount: d.amount })

            let hdata = {
                date: context.state.invoice_date,
                due_date: context.state.invoice_due_date,
                note: context.state.invoice_note,
                memo: context.state.invoice_memo,
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

            return context.dispatch("system/postme", {
                url: "sales/invoice/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('invoice/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])

                    return d
                },
                failback: function(e) {
                    alert(e)
                    return e
                }
            }, { root: true })
        },

        async search_dp(context) {
            let prm = {
                search: context.state.search,
                page: 1,
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
                url: "finance/paymentdp/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_dps", d.records)
                }
            }, { root: true })
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
                    dps.push({ id: d.dp.dp_id, amount: d.amount })

            let hdata = {
                date: context.state.invoice_date,
                due_date: context.state.invoice_due_date,
                note: context.state.invoice_note,
                customer: context.state.selected_customer.customer_id,
                disc: context.state.invoice_disc,
                discrp: context.state.invoice_discrp,
                shipping: context.state.invoice_shipping,
                dp: context.state.invoice_dp,
                term: context.state.selected_term.term_id,
                proforma: "Y"
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds),
                dps: JSON.stringify(dps),
                sales_id: context.rootState.sales.selected_sales.sales_id
            }
            if (context.state.edit) prm.invoice_id = context.state.invoice_id

            context.dispatch("system/postme", {
                url: "sales/sales/save_proforma",
                prm: prm,
                callback: function(d) {
                    if (context.rootState.invoice)
                        context.dispatch('invoice/search', null, { root: true })
                    if (context.rootState.sales)
                        context.dispatch('sales/search', null, { root: true })

                    context.commit('set_common', ['dialog_proforma', false])
                    context.commit('set_common', ['save', false])

                    if (context.rootState.sales) {
                        // console.log(d)
                        // this.select(x)
                        // let so = x
                        context.commit('set_report_url', context.rootState.invoice.URL + "report/one_sales_002?proforma=Y&id=" + d.sales_id, { root: true })
                        context.commit('set_dialog_print', true, { root: true })
                            // this.$store.commit('set_dialog_print', true)

                        // context.commit('')
                    }
                }
            }, { root: true })
        }
    }
}