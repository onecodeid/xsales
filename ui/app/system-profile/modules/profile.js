// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_profile.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        
        user: {},
        user_fullname: '',
        user_fulladdress: '',        
        user_phone: '',
        user_email: '',
        user_postcode: '',
        user_level: '',
        user_password: '',
        user_password_confirm: '',
        user_old_password: '',

        provinces : [],
        selected_province: null,
        cities: [],
        selected_city: null,
        districts: [],
        selected_district: null,
        villages: [],
        selected_village: null,

        tabs: [1,2],
        selected_tab: 1,
        snackbar: false,
        snackbar_text: '',
        alert_password: false
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

        update_search_status(state, val) {
            state.search_status = val
        },

        set_cities (state, v) { state.cities = v },
        set_selected_city (state, v) { state.selected_city = v },

        set_villages (state, v) { state.villages = v },
        set_selected_village (state, v) { state.selected_village = v },

        set_districts (state, v) {  state.districts = v },
        set_selected_district (state, v) { state.selected_district = v },

        set_provinces (state, v) { state.provinces = v },
        set_selected_province (state, v) { state.selected_province = v },

        set_user (state, v) { state.user = v }
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
                    context.commit('set_common', ['user_fullname', pr.user_fullname])
                    context.commit('set_common', ['user_fulladdress', pr.user_fulladdress])
                    context.commit('set_common', ['user_phone', pr.user_phone])
                    context.commit('set_common', ['user_email', pr.user_email])
                    context.commit('set_common', ['user_postcode', pr.user_postcode])
                    context.commit('set_common', ['user_level', pr.user_level])

                    context.commit('set_user', pr)
                    context.dispatch('search_province', {})
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
                let prm = {token : one_token(), search:context.state.search_province}
                let resp = await api.search_province(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ['search_province_status', 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_province_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_provinces", resp.data.records)
                    // if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_ProvinceID == context.state.user.province_id) {
                                context.commit('set_selected_province', pv)
                                context.dispatch('search_city')
                            }
                    // }
                }
            } catch (e) {
                context.commit("set_common", ["search_province_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_city(context) {
            context.commit("set_common", ["search_city_status", 1])
            try {
                let prm = { token : one_token(), search:context.state.search_city, province_id:context.state.selected_province.M_ProvinceID }
                let resp = await api.search_city(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_city_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_city_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_cities", resp.data.records)
                    // if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_CityID == context.state.user.city_id) {
                                context.commit('set_selected_city', pv)
                                context.dispatch('search_district')
                            }
                    // }
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_district(context) {
            context.commit("set_common", ["search_district_status", 1])
            try {
                let prm = { token : one_token(), search:context.state.search_district, city_id:context.state.selected_city.M_CityID }
                let resp = await api.search_district(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_district_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_district_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_districts", resp.data.records)
                    // if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_DistrictID == context.state.user.district_id) {
                                context.commit('set_selected_district', pv)
                                context.dispatch('search_village')
                            }
                    // }
                }
            } catch (e) {
                context.commit("set_common", ["search_district_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async search_village(context) {
            context.commit("set_common", ["search_village_status", 1])
            try {
                let prm = { token : one_token(), search:context.state.search_village, district_id:context.state.selected_district.M_DistrictID }
                let resp = await api.search_village(prm)
                if (resp.status != "OK") {
                    context.commit("set_common", ["search_village_status", 3])
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_village_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_villages", resp.data.records)
                    // if (context.state.edit) {
                        for (let pv of resp.data.records)
                            if (pv.M_KelurahanID == context.state.user.village_id) {
                                context.commit('set_selected_village', pv)
                            }
                    // }
                }
            } catch (e) {
                context.commit("set_common", ["search_village_status", 3])
                context.commit("update_search_error_message", e.message)
            }
        },

        async save_profile(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                prm.user_fullname = context.state.user_fullname
                prm.user_fulladdress = context.state.user_fulladdress
                prm.user_phone = context.state.user_phone
                prm.user_email = context.state.user_email
                prm.user_postcode = context.state.user_postcode
                prm.user_village_id = context.state.selected_village.M_KelurahanID

                let resp = await api.save_profile(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    
                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', 'Data Profile berhasil diperbarui'])
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async save_password(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                prm.user_password = context.state.user_password
                prm.user_old_password = context.state.user_old_password

                let resp = await api.save_password(prm)
                console.log(resp)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                    context.commit('set_common', ['alert_password', true])
                } else {
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                    
                    context.commit('set_common', ['snackbar', true])
                    context.commit('set_common', ['snackbar_text', 'Data Password berhasil diperbarui'])
                    context.commit('set_common', ['user_password', ''])
                    context.commit('set_common', ['user_old_password', ''])
                    context.commit('set_common', ['user_password_confirm', ''])
                    context.commit('set_common', ['alert_password', false])
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}