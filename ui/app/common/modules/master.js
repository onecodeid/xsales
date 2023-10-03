// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
import { one_token, current_date, PPN } from "../../assets/js/global.js?t=asd"

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
        edits: '',
        ppn: PPN,
        
        saless: [],
        total_sales: 0,
        
        sales_id: 0,
        sales_note: '',
        sales_memo: '',
        sales_number: '',
        sales_ref: '',
        sales_ppn: 'N',
        sales_disc: 0,
        sales_discrp: 0,
        sales_disctype: 'R',
        sales_date: current_date(),
        sales_shipping: 0,
        sales_grandtotal: 0,

        customers: [],
        selected_customer: null,

        staffs: [],
        selected_staff: null,

        offers: [],
        selected_offer: null,

        items: [],
        selected_item: null,

        addresses: [],
        selected_address: null,

        paymentplans: [],
        selected_paymentplan: null,

        details: [],
        detail_default: {item:null,qty:0,price:0,disc:0,discrp:0,ppn:'N',ppn_amount:0,total:0,subtotal:0,disctype:'P'},

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

        set_offers(state, val) {
            state.offers = val
        },

        set_selected_offer(state, val) {
            state.selected_offer = val
        },

        set_addresses(state, val) {
            state.addresses = val
        },

        set_selected_address(state, val) {
            state.selected_address = val
        },

        set_expeditions(state, val) {
            state.expeditions = val
        }
    },
    actions: {
        async search_expedition(context) {
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
        }
    }
}