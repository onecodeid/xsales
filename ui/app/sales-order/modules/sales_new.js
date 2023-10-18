// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
import { one_token, current_date, PPN, HOST } from "../../assets/js/global.js?t=asd"

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
        delivery: false,
        proforma: false,
        edits: '',
        ppn: PPN,
        sa: false,
        HOST: HOST,

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
        sales_dp: 0,
        sales_grandtotal: 0,
        sales_proforma: 'N',
        sales_customer_name: '',

        proforma_duedate: current_date(),
        proforma_number: '',

        customers: [],
        selected_customer: null,
        selected_tmp_customer: null,

        staffs: [],
        selected_staff: null,

        offers: [],
        selected_offer: null,

        items: [],
        selected_item: null,
        custom_item_name: '',

        addresses: [],
        selected_address: null,

        paymentplans: [],
        selected_paymentplan: null,

        terms: [],
        selected_term: null,

        expeditions: [],
        selected_expedition: null,
        expedition_name: '',
        expedition_mode: 'E',

        affiliates: [],
        selected_affiliate: null,
        affiliate_fee: 0,

        details: [],
        detail_default: { id: 0, item: null, qty: 0, price: 0, disc: 0, discrp: 0, ppn: 'N', ppn_amount: 0, total: 0, subtotal: 0, disctype: 'P', netto: 0 },

        dialog_new: false,
        dialog_itemname: false,
        tmp_address_id: 0
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

        set_expeditions(state, val) {
            state.expeditions = val
        },

        set_selected_expedition(state, val) {
            state.selected_expedition = val
        },

        set_affiliates(state, val) {
            state.affiliates = val
        },

        set_selected_affiliate(state, val) {
            state.selected_affiliate = val
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
                    if (!!context.state.selected_customer) {
                        for (let c of d.records)
                            if (c.customer_id == context.state.selected_customer.customer_id)
                                context.commit('set_selected_customer', c)
                    }
                }
            }, { root: true })

            return x
        },

        async search_staff(context) {
            let prm = {
                search: '',
                position: 'POS.ADMIN',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "systm/staff/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_staffs", d.records)
                    return d.records
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
                    context.commit("set_items", d.records)
                    return d.records
                }
            }, { root: true })
        },

        async search_offer(context) {
            let prm = {
                customer_id: context.state.selected_customer ? context.state.selected_customer.customer_id : -1,
                staff_id: context.state.selected_staff ? context.state.selected_staff.staff_id : -1,
                used: 'N',
                page: 1
            }

            if (context.state.edit)
                prm.edits = context.state.edits

            return context.dispatch("system/postme", {
                url: "sales/offer/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_offers", d)
                    return d
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
                    ds.push({ id: d.id, itemid: d.item.item_id, price: d.price, qty: d.qty, disc: disc, discrp: discrp, ppn: d.ppn, subtotal: d.total, netto: d.netto })
                }

            let hdata = {
                p_date: context.state.sales_date,
                p_number: context.state.sales_number,
                p_shipping: context.state.sales_shipping,
                p_dp: context.state.sales_dp,
                p_total: 0,
                p_disc: context.state.sales_disc,
                p_discrp: context.state.sales_discrp,
                p_ppn: context.state.sales_ppn,
                p_note: context.state.sales_note,
                p_memo: context.state.sales_memo,
                p_ref: context.state.sales_ref,
                p_customer: context.state.selected_customer.customer_id,
                p_customer_name: context.state.sales_customer_name,
                p_address: context.state.selected_address ? context.state.selected_address.address_id : 0,
                p_staff: 0, //context.state.selected_staff.staff_id,
                p_offer: context.state.selected_offer ? context.state.selected_offer.sales_id : 0,
                p_payment: context.state.selected_paymentplan ? context.state.selected_paymentplan.paymentplan_id : 0,
                p_term: context.state.selected_term ? context.state.selected_term.term_id : 0,
                p_exp: context.state.selected_expedition ? context.state.selected_expedition.expedition_id : 0,
                p_exp_name: context.state.expedition_name,
                p_aff_id: context.state.selected_affiliate ? context.state.selected_affiliate.affiliate_id : 0,
                p_aff_fee: context.state.affiliate_fee
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.sales_id = context.state.sales_id

            context.dispatch("system/postme", {
                url: "sales/sales/save",
                prm: prm,
                callback: function(d) {
                    if (context.rootState.sales)
                        context.dispatch('sales/search', null, { root: true })
                    if (context.rootState.offer)
                        context.dispatch('offer/search', null, { root: true })

                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                    if (context.state.sa)
                        window.location.replace('../')
                },

                failback : function (e) {
                    alert(e)
                }
            }, { root: true })
        },

        async search_address(context) {
            let prm = {
                search: '',
                page: 1,
                customer: context.state.selected_customer.customer_id
            }

            return context.dispatch("system/postme", {
                url: "master/deliveryaddress/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_addresses", d.records)

                    if (context.state.edit) {
                        for (let add of d.records)
                            if (add.address_id == context.rootState.sales.selected_sales.address_id)
                                context.commit('set_selected_address', add)
                    }

                    if (context.state.tmp_address_id != 0) {
                        for (let add of d.records)
                            if (add.address_id == context.state.tmp_address_id)
                                context.commit('set_selected_address', add)
                        context.commit('set_common', ['tmp_address_id', 0])
                    }

                    return d.records
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

        async search_expedition(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/expedition/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_expeditions", d.records)
                }
            }, { root: true })
        },

        async add_address(context) {
            let phones = []
            let phone = []
            for (let p of context.rootState.address.phones)
                if (p.no != "") {
                    phones.push(p)
                    phone.push(phone_format(p.no))
                }

            let addr = {
                id: 0,
                name: context.rootState.address.address_name,
                desc: context.rootState.address.address_desc,
                phones: JSON.stringify(phones),
                phone: phone.join(', '),
                postcode: context.rootState.address.address_postcode,
                pic_name: context.rootState.address.address_pic_name,
                province: context.rootState.address.selected_province.M_ProvinceID,
                city: context.rootState.address.selected_city.M_CityID,
                district: context.rootState.address.selected_district ?
                    context.rootState.address.selected_district.M_DistrictID : 0,
                village: context.rootState.address.selected_village ?
                    context.rootState.address.selected_village.M_KelurahanID : 0
            }

            let prm = { jdata: JSON.stringify(addr), customer_id: context.state.selected_customer.customer_id }

            context.dispatch("system/postme", {
                url: "master/deliveryaddress/save",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['tmp_address_id', d.address_id])
                    context.dispatch('search_address')
                    context.commit('address/set_common', ['dialog_new', false], { root: true })
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
                    return d.records
                }
            }, { root: true })
        },

        async save_proforma(context) {
            context.commit('set_common', ['save', true])

            let prm = {
                hdata: JSON.stringify({
                    due_date: context.state.proforma_duedate,
                    proforma: "Y"
                }),
                sales_id: context.rootState.sales.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url: "sales/sales/save_proforma",
                prm: prm,
                callback: function(d) {
                    context.dispatch('sales/search', null, { root: true })

                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])

                    // if (context.rootState.sales) {
                    // console.log(d)
                    // this.select(x)
                    // let so = x
                    // context.commit('set_report_url', context.rootState.invoice.URL+"report/one_sales_002?proforma=Y&id="+d.sales_id, {root:true})
                    // context.commit('set_dialog_print', true, {root:true})
                    // this.$store.commit('set_dialog_print', true)

                    // context.commit('')
                    // }
                }
            }, { root: true })
        },

        async search_affiliate(context) {
            let prm = {
                search: '',
                page: 1
            }

            return context.dispatch("system/postme", {
                url: "master/affiliate/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_affiliates", d.records)
                    return d.records
                }
            }, { root: true })
        },

        async save_item_name(context) {
            let prm = {
                token: one_token(),
                id: context.state.selected_item.detail_id,
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