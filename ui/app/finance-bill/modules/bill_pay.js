// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, URL, lastmonth_date, current_date } from "../../assets/js/global.js?t=123"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',
        URL: URL,
        dialog_new: false,
        current_date: current_date(),
        pay_date: lastmonth_date(),
        dialog_pay: false,
        edit: false,
        sa: false,

        selected_account: null,
        bill_id: 0,
        bill_number: '',
        bill_date: '',
        bill_total: 0,
        bill_grand_total: 0,
        bill_paid: 0,
        bill_unpaid: 0,
        pay_amount: 0,
        pay_id: 0,
        pay_note: '',
        pay_number: '',
        pay_date: '',

        selected_bill: null,
        selected_vendor: null,

        // disc
        use_disc: false,
        selected_disc_account: null,
        disc_note: '',
        disc_amount: 0,

        // memos
        use_memo: false,
        memos: [],
        selected_memos: [{ selected_memo: null, amount: 0 }],
        default_memo: { selected_memo: null, amount: 0 }
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
        }
    },
    actions: {
        async save(context) {
            // SET pay_receipt = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.pay_receipt'));

            let prm = {
                token: one_token(),
                pay_id: context.state.pay_id,
                hdata: {
                    pay_date: context.state.pay_date,
                    pay_amount: context.state.pay_amount,
                    pay_disc: context.rootState.disc.disc,
                    pay_discrp: context.rootState.disc.discrp,
                    pay_discamount: parseFloat(context.state.disc_amount),
                    pay_note: context.state.pay_note,
                    pay_receipt: context.state.pay_receipt,
                    pay_credit_account: context.state.selected_account ? context.state.selected_account.account_id : 0,
                    pay_disc_account: context.state.selected_disc_account ? context.state.selected_disc_account.account_id : 0,
                    bill_id: context.state.bill_id
                }
            }

            let memos = []
            let memo_amount = 0
            for (let m of context.state.selected_memos) {
                if (m.selected_memo && m.amount > 0) {
                    memos.push({ memo_id: m.selected_memo.memo_id, memo_number: m.selected_memo.memo_number, amount: m.amount })
                    memo_amount += parseFloat(m.amount)
                }
            }
            prm.hdata.memos = JSON.stringify(memos)
            prm.hdata.memo_amount = memo_amount
            prm.hdata = JSON.stringify(prm.hdata)

            return context.dispatch("system/postme", {
                url: "finance/billpay2/save",
                prm: prm,
                callback: function(d) {
                    // context.commit("set_object", ["selected_memo", d])
                    return d
                },
                failback: function(e) {
                    alert(e)
                }
            }, { root: true })
        },

        async search_memo(context) {
            let prm = {
                token: one_token(),
                vendor_id: context.state.selected_vendor.vendor_id
            }

            return context.dispatch("system/postme", {
                url: "finance/memodebit/search_av",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                    context.commit("set_object", ["memos", d])
                        // return d
                }
            }, { root: true })
        },

        async search_bill_id(context) {
            let prm = {
                token: one_token(),
                bill_id: context.state.bill_id
            }

            return context.dispatch("system/postme", {
                url: "sales/bill/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_memo", d])
                    return d
                }
            }, { root: true })
        },

        async search_pay_id(context) {
            let prm = {
                search: context.state.search,
                pay_id: context.state.pay_id
            }

            return context.dispatch("system/postme", {
                url: "finance/billpay2/search_id",
                prm: prm,
                callback: function(d) {
                    console.log(d)
                        // let accs = []
                        // for (let acc of d.records) {
                        //     if (acc.parent == "0") acc.parent = false
                        //     accs.push(acc)
                        // }

                    // context.commit("set_object", ["cash_accounts", accs])
                }
            }, { root: true })
        }
    }
}