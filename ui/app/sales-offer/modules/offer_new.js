// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
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
        
        saless: [],
        total_sales: 0,
        
        sales_id: 0,
        sales_note: '',
        sales_memo: '',
        sales_number: '',
        sales_ppn: 'N',
        sales_date: current_date(),
        sales_franco: '',
        sales_delivery: '',
        sales_validity: '',
        sales_used: 'N',
        sales_shipping: 0,
        sales_grandtotal: 0,

        customers: [],
        selected_customer: null,

        staffs: [],
        selected_staff: null,

        items: [],
        selected_item: null,

        packs : [],
        units : [],

        leadtypes: [],
        selected_leadtype: null,

        paymentplans: [],
        selected_paymentplan: null,

        terms: [],
        selected_term: null,

        details: [],
        detail_default: {item:null,qty:0,price:0,disc:0,discrp:0,ppn:'N',total:0,disctype:'P',other:'N',other_name:'',pack:null,unit:null,tooltip:true},

        dialog_new: false
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

        set_sales_note(state, val) {
            state.sales_note = val
        },

        set_customers(state, val) {
            state.customers = val
        },

        set_selected_customer(state, val) {
            state.selected_customer = val
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
        },

        set_terms(state, val) {
            state.terms = val
        },

        set_selected_term(state, val) {
            state.selected_term = val
        },

        set_leadtypes(state, val) {
            state.leadtypes = val
        },

        set_selected_leadtype(state, val) {
            state.selected_leadtype = val
        },

        set_packs(state, val) {
            state.packs = val
        },

        set_units (state, v) {
            state.units = v
        }
    },
    actions: {
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
                    if (!!context.state.selected_customer) {
                        for (let c of d.records)
                            if (c.customer_id == context.state.selected_customer.customer_id)
                                context.commit('set_selected_customer', c)
                    }
                }
            }, {root:true})
        },

        async search_staff(context) {
            let prm = {
                search : '',
                position : 'POS.ADMIN',
                page : 1
            }

            context.dispatch("system/postme", {
                url:"systm/staff/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_staffs", d.records)
                }
            }, {root:true})
        },

        async search_item(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/item/search_dd",
                prm:prm,
                callback:function(d) {
                    context.commit("set_items", d.records)
                }
            }, {root:true})
        },

        async save(context) {
            context.commit('set_common', ['save', true])
            let ds = []
            let disc, discrp
            for (let d of context.state.details)
                if ((d.qty > 0) && ((!!d.item && d.other!='Y') || (d.other=='Y' && d.other_name!=''))) {
                    disc = d.disctype=='P' ? d.disc : 0
                    discrp = d.disctype=='R' ? d.discrp : 0
                    ds.push({itemid:(d.other!='Y'?d.item.item_id:0),other:d.other,other_name:d.other_name,price:d.price,qty:d.qty,
                        disc:disc,discrp:discrp,ppn:d.ppn,subtotal:d.total,
                        pack:d.pack?d.pack.pack_id:0,unit:d.unit?d.unit.unit_id:0})
                }
                    
            let hdata = {
                p_date:context.state.sales_date,
                p_shipping:context.state.sales_shipping,
                p_total:0,
                p_ppn: context.state.sales_ppn,
                p_note: context.state.sales_note,
                p_memo: context.state.sales_memo,
                p_customer: context.state.selected_customer.customer_id,
                p_leadtype: context.state.selected_leadtype.leadtype_id,
                p_staff: context.state.selected_staff.staff_id,
                p_franco: context.state.sales_franco,
                p_delivery: context.state.sales_delivery,
                p_validity: context.state.sales_validity,
                p_payment: context.state.selected_paymentplan?context.state.selected_paymentplan.paymentplan_id:0,
                p_term: context.state.selected_term?context.state.selected_term.term_id:0
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.offer_id = context.state.sales_id

            context.dispatch("system/postme", {
                url:"sales/offer/save",
                prm:prm,
                callback:function(d) {
                    context.dispatch('sales/search', null, {root:true})
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, {root:true})
        },

        async search_paymentplan(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/paymentplan/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_paymentplans", d.records)
                }
            }, {root:true})
        },

        async search_leadtype(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/leadtype/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_leadtypes", d.records)
                }
            }, {root:true})
        },

        async search_pack(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/pack/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_packs", d.records)
                }
            }, {root:true})
        },

        async search_unit(context) {
            let prm = {
                search : context.state.search,
                page : 1
            }

            context.dispatch("system/postme", {
                url:"master/unit/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_units", d.records)
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
        }
    }
}