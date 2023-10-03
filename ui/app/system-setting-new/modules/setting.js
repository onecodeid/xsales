// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_setting.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: true,
        search_status: 0,
        search_error_message: '',
        search: '',
        setting: {},

        search_city_status: 0,
        search_city: '',
        cities: [],
        selected_city: null,

        search_village_status: 0,
        search_village: '',
        villages: [],
        selected_village: null,

        search_district_status: 0,
        search_district: '',
        districts: [],
        selected_district: null,

        search_province_status: 0,
        search_province: '',
        provinces: [],
        selected_province: null,

        snackbar: false,
        snackbar_text: '',
        alert_password: false,

        company_name: '',
        company_address: '',
        company_phone: '',
        target_weekly: 0,
        payment_expired: 24,

        tabs: [{ id: "SETTING.GLOBAL", label: 'Global Setting' }],
        selected_tab: null,

        ppn: 10
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

        update_search(state, search) {
            state.search = search
        },

        update_search_status(state, val) {
            state.search_status = val
        },

        set_cities(state, v) { state.cities = v },
        set_selected_city(state, v) { state.selected_city = v },

        set_villages(state, v) { state.villages = v },
        set_selected_village(state, v) { state.selected_village = v },

        set_districts(state, v) { state.districts = v },
        set_selected_district(state, v) { state.selected_district = v },

        set_provinces(state, v) { state.provinces = v },
        set_selected_province(state, v) { state.selected_province = v },

        set_setting(state, v) { state.setting = v },
        set_selected_tab(state, v) { state.selected_tab = v }
    },
    actions: {
        async search(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.search(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    let pr = resp.data
                    context.commit('set_common', ['company_name', pr.S_SystemCompanyName])
                    context.commit('set_common', ['company_address', pr.S_SystemCompanyAddress])
                    context.commit('set_common', ['company_phone', pr.S_SystemCompanyPhone])
                    context.commit('set_common', ['target_weekly', pr.S_SystemTargetWeekly])
                    context.commit('set_common', ['payment_expired', pr.S_SystemPaymentExpired])

                    context.commit('set_setting', pr)
                    context.dispatch('search_province')
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_province(context) {
            context.commit("set_common", ["search_province_status", 1])
            try {
                let prm = { token: one_token(), search: context.state.search_province }
                let resp = await api.search_province(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_province_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_province_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_provinces", resp.data.records)
                    if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_ProvinceID == context.state.setting.S_SystemCompanyM_ProvinceID) {
                                context.commit('set_selected_province', pv)
                                context.dispatch('search_city')
                            }
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_province_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_city(context) {
            context.commit("set_common", ["search_city_status", 1])
            try {
                let prm = { token: one_token(), search: context.state.search_city, province_id: context.state.selected_province.M_ProvinceID }
                let resp = await api.search_city(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_city_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_city_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_cities", resp.data.records)
                    if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_CityID == context.state.setting.S_SystemCompanyM_CityID) {
                                context.commit('set_selected_city', pv)
                                context.dispatch('search_district')
                            }
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_district(context) {
            context.commit("set_common", ["search_district_status", 1])
            try {
                let prm = { token: one_token(), search: context.state.search_district, city_id: context.state.selected_city.M_CityID }
                let resp = await api.search_district(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_district_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_district_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_districts", resp.data.records)
                    if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_DistrictID == context.state.setting.S_SystemCompanyM_DistrictID) {
                                context.commit('set_selected_district', pv)
                                context.dispatch('search_village')
                            }
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_district_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_village(context) {
            context.commit("set_common", ["search_village_status", 1])
            try {
                let prm = { token: one_token(), search: context.state.search_village, district_id: context.state.selected_district.M_DistrictID }
                let resp = await api.search_village(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_village_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_village_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_villages", resp.data.records)
                    if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_KelurahanID == context.state.setting.S_SystemCompanyM_KelurahanID) {
                                context.commit('set_selected_village', pv)
                            }
                    }
                    context.commit('set_common', ['edit', false])
                }
            } catch (e) {
                context.commit("set_common", ["search_village_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async save_setting(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                prm.company_name = context.state.company_name
                prm.company_address = context.state.company_address
                prm.company_phone = context.state.company_phone
                prm.province_id = context.state.selected_province.M_ProvinceID
                prm.city_id = context.state.selected_city.M_CityID
                prm.district_id = context.state.selected_district.M_DistrictID
                prm.kelurahan_id = context.state.selected_village.M_KelurahanID
                prm.payment_expired = context.state.payment_expired
                prm.target_weekly = context.state.target_weekly

                let resp = await api.save_setting(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")

                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', 'Data Setting berhasil diperbarui'])
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async get_conf(context) {
            context.dispatch("system/postme", {
                prm: {},
                url: "systm/conf/get_conf",
                callback: function(d) {
                    context.commit('set_common', ['ppn', d.ppn])
                }
            }, { root: true })
        },

        async save_conf(context) {
            context.dispatch("system/postme", {
                prm: {
                    token: one_token(),
                    ppn: context.state.ppn
                },
                url: "systm/conf/save",
                callback: function(d) {
                    context.commit('set_common', ['snackbar_text', 'Konfigurasi Global berhasil diperbarui'])
                    context.commit('set_common', ['snackbar', true])
                }
            }, { root: true })
        }
    }
}