// 1 => LOADING
// 2 => DONE
// 3 => ERROR
// import * as api from "./api_sales.js"
import { one_token, current_date, lastmonth_date, one_user, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        URL: URL,
        one_token: one_token(),
        save: false,
        
        saless: [],
        total_sales: 0,
        total_sales_page: 0,
        selected_sales: {},
        selected_leadtype: null,

        selected_staff: null,

        s_date: lastmonth_date(),
        e_date: current_date(),

        user: one_user(),

        current_page: 1
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

        update_search(state, search) {
            state.search = search
        },

        update_saless(state, data) {
            state.saless = data
        },

        set_selected_sales(state, val) {
            state.selected_sales = val
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        update_total_sales(state, val) {
            state.total_sales = val
        },

        update_total_sales_page(state, val) {
            state.total_sales_page = val
        },

        update_current_page(state, val) {
            state.current_page = val
        },

        set_selected_leadtype(state, val) {
            state.selected_leadtype = val
        },

        set_selected_staff(state, val) {
            state.selected_staff = val
        }
    },
    actions: {
        async search(context) {
            let prm = {
                token : one_token(),
                search : context.state.search,
                page : context.state.current_page,
                sdate : context.state.s_date,
                edate : context.state.e_date,
                lead : context.state.selected_leadtype?context.state.selected_leadtype.leadtype_id:0,
                staff : context.state.selected_staff?context.state.selected_staff.staff_id:0
            }

            // let ex = ["ONE.GROUP.ADMIN","ONE.GROUP.AVENGER"]
            // if (ex.indexOf(context.state.user.group_code)>-1)
            //     prm.staff = context.state.user.staff_id

            context.dispatch("system/postme", {
                url:"sales/offer/search",
                prm:prm,
                callback:function(d) {
                    context.commit("update_saless", d.records)
                    context.commit("update_total_sales", d.total)
                    context.commit("update_total_sales_page", d.total_page)
                }
            }, {root:true})
        },

        // async searchx(context) {
        //     context.commit("update_search_status", 1)
        //     try {
        //         let prm = {}
        //         prm.token = one_token()
        //         prm.search = context.state.search
        //         prm.page = context.state.current_page
        //         prm.sdate = context.state.s_date
        //         prm.edate = context.state.e_date
        //         let resp = await api.search(prm)
                
        //         if (resp.status != "OK") {
        //             context.commit("update_search_status", 3)
        //             context.commit("update_search_error_message", resp.message)
        //         } else {
        //             context.commit("update_search_status", 2)
        //             context.commit("update_search_error_message", "")
        //             let data = {
        //                 records: resp.data.records,
        //                 total: resp.data.total,
        //                 total_page: resp.data.total_page
        //             }

        //             context.commit("update_saless", data.records)
        //             context.commit("update_total_sales", data.total)
        //             context.commit("update_total_sales_page", data.total_page)
        //         }
        //     } catch (e) {
        //         context.commit("update_search_status", 3)
        //         context.commit("update_search_error_message", e.message)
        //         console.log(e)
        //     }
        // },

        async del(context) {
            let prm = {
                id : context.state.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url:"sales/offer/del",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        },

        async post(context) {
            let prm = {
                id : context.state.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url:"trans/offer/post",
                prm:prm,
                callback:function(d) {
                    context.dispatch("search")
                }
            }, {root:true})
        },

        async convert_to_sales(context) {
            context.commit('set_common', ['save', true])

            let prm = {
                offer_id: context.state.selected_sales.sales_id
            }

            context.dispatch("system/postme", {
                url:"sales/offer/convert_to_sales",
                prm:prm,
                callback:function(d) {
                    context.dispatch('search')
                    context.commit('set_common', ['save', false])
                }
            }, {root:true})
        }
    }
}