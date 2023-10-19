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

        payment_date: current_date(),
        payment_total: 0,
        payment_paid: 0,
        payment_unpaid: 0,
        payment_amount: 0,
        payment_note: '',
        payment_id: 0,
        selected_payment: null,

        payment_types: [],
        selected_payment_type: null,

        dialog: false,
        save: false
    },
    
    mutations : {
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
    },
    actions: {
        async search_paymenttype(context) {
            let prm = { search: "", page: 1 }

            let c = context.dispatch("system/postme", {
                url: "master/paymenttype/search_autocomplete",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["payment_types", d])
                    return d
                }
            }, { root: true })

            return c
        },

        async save(context) {
            let __s = context.state
            context.commit('set_object', ['save', true])

            let hdata = {
                pay_sales: context.rootState.sales.selected_sales.sales_id,
                pay_date: __s.payment_date,
                pay_type: __s.selected_payment_type.paymenttype_id,
                pay_amount: __s.payment_amount,
                pay_note: context.state.payment_note
            }

            let prm = {
                hdata: JSON.stringify(hdata)
            }
            if (context.state.edit) prm.pay_id = context.state.payment_id

            return context.dispatch("system/postme", {
                url: "finance/spay/save",
                prm: prm,
                callback: function(d) {
                    return d
                    // if (context.rootState.sales)
                    //     context.dispatch('sales/search', null, { root: true })
                    // if (context.rootState.offer)
                    //     context.dispatch('offer/search', null, { root: true })

                    // context.commit('set_common', ['dialog_new', false])
                    // context.commit('set_common', ['save', false])
                    // if (context.state.sa)
                    //     window.location.replace('../')
                },

                failback : function (e) {
                    alert(e)
                }
            }, { root: true })
        },

        async del(context) {
            let prm = {
                pay_id: context.state.selected_payment.pay_id
            }

            return context.dispatch("system/postme", {
                url: "finance/spay/delete",
                prm: prm,
                callback: function(d) {
                    return d
                }
            }, { root: true })
        }

        
    }

}