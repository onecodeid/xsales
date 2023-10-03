// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_report.js"
import { one_token, URL, page_sizes } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        token: one_token(),
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',
        blank_sheet: false,
        invoice_all: false,
        page_sizes: page_sizes(),

        report_url: '',

        dialog: {
            'vp-001': false,
            'vp-002': false,
            'sales-003': false,
            'sales-008': false,
            'sales-009': false,
            'sales-011': false,
            'sales-012': false,
            'sales-013': false,
            'sales-014': false,
            'iv-001': false,
            'iv-003': false,
            'iv-004': false,
            'fin-001': false,
            'fin-002': false,
            'fin-003': false,
            'fin-005': false,
            'fin-006': false,
            'fin-007': false,
            'pur-002': false
        },
        sdate: new Date().toISOString().substr(0, 10),
        edate: new Date().toISOString().substr(0, 10),
        sday: 1,
        eday: 31,

        admins: [],
        selected_admin: null,

        customers: [],
        selected_customer: null,
        search_customer: '',

        regions: [],
        selected_region: null,
        search_region: '',

        staffs: [],
        selected_staff: null,

        customer_levels: [],
        selected_customer_level: null,

        customer_b2bs: [{ id: 'Y', text: 'Customer Perusahaan / B2B' }, { id: 'N', text: 'Customer Perorangan / B2C' }],
        selected_customer_b2b: { id: 'Y', text: 'Customer Perusahaan / B2B' },

        orders: [],
        selected_order: null,

        provinces: [],
        selected_province: null,
        cities: [],
        selected_city: null,

        items: [],
        selected_item: null,
        search_item: '',

        item_types: [{ value: 'N', text: 'Item' }, { value: 'Y', text: 'Paket' }],
        selected_item_type: null,

        expeditions: [],
        selected_expedition: null,

        warehouses: [],
        selected_warehouse: null,

        months: [],
        selected_month: null,

        accounts: [],
        selected_account: null,

        endyears: [],
        selected_endyear: null
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

        set_dialog(state, v) {
            let x = state.dialog;
            x[v[0]] = v[1];
            state.dialog = x
        },
        update_search_error_message(state, msg) { state.search_error_message = msg },
        update_search(state, search) { state.search = search },

        set_admins(state, v) { state.admins = v },
        set_selected_admin(state, v) { state.selected_admin = v },

        set_customers(state, data) { state.customers = data },
        set_selected_customer(state, val) { state.selected_customer = val },

        set_regions(state, data) { state.regions = data },
        set_selected_region(state, val) { state.selected_region = val },

        set_staffs(state, data) { state.staffs = data },
        set_selected_staff(state, val) { state.selected_staff = val },

        set_customer_levels(state, data) { state.customer_levels = data },
        set_selected_customer_level(state, val) { state.selected_customer_level = val },

        set_selected_customer_b2b(state, val) { state.selected_customer_b2b = val },

        set_orders(state, data) { state.orders = data },
        set_selected_order(state, val) { state.selected_order = val },

        set_provinces(state, data) { state.provinces = data },
        set_selected_province(state, val) { state.selected_province = val },

        set_cities(state, data) { state.cities = data },
        set_selected_city(state, val) { state.selected_city = val },

        set_expeditions(state, data) { state.expeditions = data },
        set_selected_expedition(state, val) { state.selected_expedition = val },

        set_warehouses(state, data) { state.warehouses = data },
        set_selected_warehouse(state, val) { state.selected_warehouse = val },

        set_items(state, data) { state.items = data },
        set_selected_item(state, val) { state.selected_item = val },

        set_months(state, data) { state.months = data },
        set_selected_month(state, val) { state.selected_month = val },

        set_selected_item_type(state, val) { state.selected_item_type = val }
    },
    actions: {
        async search_admins(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token() }
                let resp = await api.search_admins(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_admins", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_customer(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: context.state.search_customer }
                let resp = await api.search_customer(prm)

                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_customers", resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_customer_level(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token() }
                let resp = await api.search_customer_level(prm)

                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_customer_levels", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_province(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: context.state.search_province }
                let resp = await api.search_province(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_provinces", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_city(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: context.state.search_city, province_id: context.state.selected_province.M_ProvinceID }
                let resp = await api.search_city(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_cities", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_expedition(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token() }
                let resp = await api.search_expedition(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_expeditions", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_item(context) {
            let prm = {
                search: '',
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

        async search_item_old(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: '', page: -1 }
                let resp = await api.search_item(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_items", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_region(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: context.state.search_region }
                let resp = await api.search_region(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_regions", resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_staff(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = {
                    token: one_token(),
                    search: '',
                    position: 'POS.ADMIN',
                    page: 1
                }
                let resp = await api.search_staff(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_staffs", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_month(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: '', page: -1 }
                let resp = await api.search_month(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_months", resp.data)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_warehouse(context) {
            context.commit("set_common", ['search_status', 1])
            try {
                let prm = { token: one_token(), search: '', page: 1 }
                let resp = await api.search_warehouse(prm)
                if (resp.status != "OK") {
                    context.dispatch('search_status_not_ok', resp)
                } else {
                    context.dispatch('search_status_ok')
                    context.commit("set_warehouses", resp.data.records)
                }
            } catch (e) {
                context.dispatch('search_status_not_ok', e)
            }
        },

        async search_account(context) {
            let prm = {
                search: "",
                page: 1,
                limit: 1000
            }

            context.dispatch("system/postme", {
                url: "master/account/search",
                prm: prm,
                callback: function(d) {
                    let accs = []
                    for (let acc of d.records) {
                        if (acc.parent == "0") acc.parent = false
                        accs.push(acc)
                    }

                    context.commit("set_object", ["accounts", accs])
                }
            }, { root: true })
        },

        async search_endyear(context) {
            let prm = {}

            context.dispatch("system/postme", {
                url: "report/report/search_end_years",
                prm: prm,
                callback: function(d) {
                    context.commit("set_object", ["endyears", d])
                }
            }, { root: true })
        },


        async search_status_not_ok(context, d) {
            context.commit("set_common", ['search_status', 3])
            context.commit("update_search_error_message", d.message)
        },

        async search_status_ok(context) {
            context.commit("set_common", ['search_status', 2])
            context.commit("update_search_error_message", "")
        }
    }
}