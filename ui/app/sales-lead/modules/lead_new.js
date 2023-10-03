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

        lead_id: 0,
        lead_note: '',
        lead_memo: '',
        lead_number: '',
        lead_ppn: 'N',
        lead_date: current_date(),

        categories: [],
        selected_category: null,

        prospects: [],
        selected_prospect: null,

        customer_types: [{ val: 'b2b', text: 'B2B' }, { val: 'b2c', text: 'Personal' }],

        staffs: [],
        selected_staff: null,

        items: [],
        selected_item: null,

        details: [],
        detail_default: { item: null, qty: 0, price: 0, disc: 0, discrp: 0, ppn: 'N', total: 0, disctype: 'P' },

        dialog_new: false,
        values: {},
        jooo: []
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

        update_search_error_message(state, msg) {
            state.search_error_message = msg
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_lead_note(state, val) {
            state.lead_note = val
        },

        set_categories(state, val) {
            state.categories = val
        },

        set_selected_category(state, val) {
            state.selected_category = val
        },

        set_prospects(state, val) {
            state.prospects = val
        },

        set_selected_prospect(state, val) {
            state.selected_prospect = val
        },

        set_staffs(state, val) {
            state.staffs = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
        },

        set_jooo(state, val) {
            state.jooo = val
        },

        set_values(state, val) {
            state.values = val
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

        async search_category(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/category/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_categories", d.records)

                    // SET VALUES
                    let leads = JSON.parse(JSON.stringify(context.rootState.lead.leads))
                    let values = {}

                    for (let i in leads) {
                        values = {}

                        for (let ctg of context.state.categories)
                            values['Cx' + ctg.category_id] = { b2b: 0, b2c: 0, total: 0 }

                        for (let ppt of context.state.prospects)
                            values['Px' + ppt.prospect_id] = { b2b: 0, b2c: 0, total: 0 }

                        for (let d of leads[i].details) {
                            if (d.d_type == 'P') {
                                if (values[d.d_type + 'x' + d.d_pid]) {
                                    values[d.d_type + 'x' + d.d_pid] = { b2b: Math.round(d.d_b2b), b2c: Math.round(d.d_b2c), total: Math.round(d.d_b2b) + Math.round(d.d_b2c) }
                                }

                            } else {
                                if (values[d.d_type + 'x' + d.d_cid])
                                    values[d.d_type + 'x' + d.d_cid] = { b2b: Math.round(d.d_b2b), b2c: Math.round(d.d_b2c), total: Math.round(d.d_b2b) + Math.round(d.d_b2c) }
                            }
                        }
                        // console.log(values)
                        leads[i].values = JSON.parse(JSON.stringify(values))
                            // console.log('a')
                        console.log(leads[i].values)
                    }
                    context.commit('lead/update_leads', leads, { root: true })
                }
            }, { root: true })
        },

        async search_prospect(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/prospect/search_dd",
                prm: prm,
                callback: function(d) {
                    context.commit("set_prospects", d.records)
                    context.dispatch("search_category")

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
            for (let d in context.state.values) {
                let e = d.split('x')
                ds.push({
                    type: e[0],
                    did: e[1],
                    b2b: Math.round(context.state.values[d].b2b),
                    b2c: Math.round(context.state.values[d].b2c)
                })
            }

            let hdata = {
                p_date: context.state.lead_date,
                p_note: context.state.lead_note,
                p_staff: context.state.selected_staff.staff_id
            }

            let prm = {
                hdata: JSON.stringify(hdata),
                jdata: JSON.stringify(ds)
            }
            if (context.state.edit) prm.lead_id = context.state.lead_id

            context.dispatch("system/postme", {
                url: "sales/lead/save",
                prm: prm,
                callback: function(d) {
                    context.dispatch('lead/search', null, { root: true })
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        }
    }
}