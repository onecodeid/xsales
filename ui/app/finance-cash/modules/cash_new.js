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
        save: false,
        edit: false,
        dialog_new: false,
        dialogUploadResume: false,
        current_date: current_date(),
        sa: false,

        accounts: [],
        cash_accounts: [],
        total_account: 0,
        total_account_page: 0,
        selected_account: null,
        selected_account_to: null,
        // tmp
        tmp_acc_id: 0,
        tmp_acc_to_id: 0,

        cash_type_code: 'CASH.RECEIVE',
        cash_type_name: '',
        cash_id: 0,
        cash_number: '',
        cash_from: '',
        cash_note: '',
        cash_memo: '',
        cash_amount: 0,
        cash_disc: 0,
        cash_discrp: 0,
        cash_tax: 0,
        cash_taxrp: 0,

        cash_img: '',
        cash_img_url: '',
        cash_img_file: '',

        taxes: [],
        selected_tax: null,

        cash_date: current_date(),

        cashes: [],
        selected_cash: null,
        total_cash: 0,
        s_date: lastmonth_date(),
        e_date: current_date(),
        current_page: 1,
        total_cash_page: 1,

        // UPLOAD
        fileObject: null,
        fileName: '',
        url: '',

        col_headers: [],
        col_date: null,
        col_to: null,
        col_acc_credit: null,
        col_acc_debit: null,
        col_desc: null,
        col_memo: null,
        col_tag: null,
        col_nominal: null,
        up_datas: [],

        // FILE TO UPLOAD
        batch_datas: [],
        batch_cnt: 0,
        batch_md5: "",

        // VERSION 2
        details: [],
        detail_cnt: 2,
        detail_default: { account: null, credit: 0, debit: 0 },

        // VERSION 3
        details_add: [],
        detail_add_cnt: 2
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

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        resetDetail(state) {
            let details = []
            let details_add = []
            for (let i = 0; i < state.detail_cnt; i++) details.push(state.detail_default)

            details_add.push({ account: (state.cash_type_code == 'CASH.PAY' ? state.selected_account : state.selected_account_to), debit: 0, credit: 0 })
            for (let i = 0; i < state.detail_add_cnt; i++) details_add.push(state.detail_default)
            state.details = details
            state.details_add = details_add
        }
    },
    actions: {
        async save(context) {
            context.commit('set_common', ['save', true])
            context.commit('set_dialog_progress', true, { root: true })

            let jdata = []
            for (let d of context.state.details) {
                if (!!d.account && (d.debit > 0 || d.credit > 0))
                    jdata.push({ account: d.account.account_id, debit: d.debit, credit: d.credit })
            }
            for (let d of context.state.details_add) {
                if (!!d.account && (d.debit > 0 || d.credit > 0))
                    jdata.push({ account: d.account.account_id, debit: d.debit, credit: d.credit })
            }

            let prm = {
                hdata: JSON.stringify({
                    cash_date: context.state.cash_date,
                    cash_from: context.state.cash_from,
                    cash_from_account: context.state.selected_account ? context.state.selected_account.account_id : 0,
                    cash_to_account: context.state.selected_account_to ? context.state.selected_account_to.account_id : 0,
                    cash_amount: context.state.cash_amount,
                    cash_disc: context.state.cash_disc,
                    cash_discrp: context.state.cash_discrp,
                    cash_tax: context.state.selected_tax ? context.state.selected_tax.tax_id : 0,
                    cash_tax_amount: context.state.selected_tax ? context.state.selected_tax.tax_amount : 0,
                    cash_note: context.state.cash_note,
                    cash_memo: context.state.cash_memo,
                    cash_tags: context.rootState.tag.selected_tagnames ? JSON.stringify(context.rootState.tag.selected_tagnames) : "[]",
                    cash_type_code: context.state.cash_type_code
                }),
                jdata: JSON.stringify(jdata),
                cash_img: context.state.cash_img
            }
            if (context.state.edit) prm.cash_id = context.state.cash_id

            return context.dispatch("system/postme", {
                url: "finance/cash/save",
                prm: prm,
                callback: function(d) {
                    context.commit('set_common', ['save', false])
                    context.commit('set_dialog_progress', false, { root: true })
                },
                failback: function(e) {
                    context.commit('set_common', ['save', false])
                    context.commit('set_dialog_progress', false, { root: true })
                }
            }, { root: true })
        },

        async saveBatch(context) {
            context.commit('set_common', ['save', true])

            return context.dispatch("system/postme", {
                url: "finance/cash/save_batch",
                prm: { hdata: JSON.stringify(context.state.batch_datas) },
                callback: function(d) {
                    console.log(d)
                    context.commit('set_common', ['save', false])
                    context.commit('set_common', ['dialog_new', false])
                    context.commit('set_common', ['dialogUploadResume', true])
                    context.commit('set_common', ['batch_cnt', d.cash_cnt])
                    context.commit('set_common', ['batch_md5', d.cash_md5])
                    context.commit('cash/set_common', ['search_md5', d.cash_md5], { root: true })
                    context.commit('cash/set_common', ['view', true], { root: true })
                    context.commit('cash/set_object', ['selected_account', null], { root: true })

                    // SET DATE FILTER
                    context.commit('cash/set_common', ['s_date', d.min_date], { root: true })
                    context.commit('cash/set_common', ['e_date', d.max_date], { root: true })

                    context.dispatch('cash/search', null, { root: true })
                },
                failback: function(e) {
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        async search(context) {
            let prm = {
                token: one_token(),
                search: context.state.search,
                page: context.state.current_page,
                sdate: context.state.s_date,
                edate: context.state.e_date
            }

            context.dispatch("system/postme", {
                url: "finance/cash/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["cashes", d.records])
                    context.commit("set_object", ["total_cash", d.total])
                    context.commit("set_object", ["total_cash_page", d.total_page])
                }
            }, { root: true })
        },

        async search_id(context) {
            let prm = {
                token: one_token(),
                cash_id: context.state.cash_id
            }

            return context.dispatch("system/postme", {
                url: "finance/cash/search_id",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["selected_cash", d])
                    return d
                }
            }, { root: true })
        },

        async search_account(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["accounts", d.records])
                }
            }, { root: true })
        },

        async search_account_cash(context) {
            let prm = {
                search: context.state.search,
                page: 1,
                group_id: 1,
                limit: 1000
            }

            return context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["cash_accounts", d.records])
                }
            }, { root: true })
        },

        async search_tax(context) {
            let prm = {
                search: context.state.search,
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/tax/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["taxes", d.records])
                }
            }, { root: true })
        },

        async search_tag(context) {
            let prm = {
                search: '',
                page: 1
            }

            context.dispatch("system/postme", {
                url: "master/tag/search",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ['tags', d.records])

                    let tags = []
                    for (let r of d.records) tags.push(r.tag_name)
                    context.commit("set_object", ['tagnames', tags])
                }
            }, { root: true })
        },
        // async search(context) {
        //     context.commit("update_search_status", 1)

        //     let prm = {}
        //     prm.token = one_token()
        //     prm.date = context.state.edate

        //     context.dispatch("system/postme", {
        //         url: "analytic/sales/mtd_projo",
        //         prm: prm,
        //         callback: function(d) {
        //             context.commit("set_sales", d.records)
        //             context.commit("set_total_sales", d.total)
        //         }
        //     }, { root: true })
        // }
        async upload(context) {
            context.commit('set_common', ['save', true])

            let formData = new FormData()
            formData.append("file", context.state.fileObject)
            console.log('formData')
            console.log(formData)
                // let prm = {
                //     file: context.state.fileObject
                // }

            return context.dispatch("system/postme", {
                url: "finance/cash/upload",
                prm: formData,
                hdr: {
                    'Content-Type': 'multipart/form-data; charset=utf-8; boundary=' + Math.random().toString().substr(2),
                    // headers: { 'Content-Type': 'multipart/form-data; charset=utf-8; boundary=' + Math.random().toString().substr(2) }
                },
                callback: function(d) {
                    context.commit('set_common', ['save', false])
                    context.commit('set_object', ['col_headers', d.records.headers])
                    context.commit('set_object', ['up_datas', d.records.data])
                },
                failback: function(e) {
                    context.commit('set_common', ['save', false])
                }
            }, { root: true })
        },

        // async uploadFile(context) {


        //     let formData = new FormData()
        //     formData.append("file", context.state.fileObject)
        //     console.log(formData)

        //     try {
        //         const response = await axios.post('../../../../api/finance/cash/upload', formData, {
        //             headers: {
        //                 "Content-Type": "multipart/form-data; charset=utf-8; boundary=" + Math.random().toString().substr(2),
        //             }
        //         });
        //         console.log(response);
        //     } catch (error) {
        //         if (error.response) { // get response with a status code not in range 2xx
        //             console.log(error.response.data);
        //             console.log(error.response.status);
        //             console.log(error.response.headers);
        //         } else if (error.request) { // no response
        //             console.log(error.request);
        //         } else { // Something wrong in setting up the request
        //             console.log('Error', error.message);
        //         }
        //         console.log(error.config);
        //     }
        // }

    }
}