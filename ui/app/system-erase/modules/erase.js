// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_erase.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        respon: {},
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
        provinces : [],
        selected_province: null,
        cities: [],
        selected_city: null,
        districts: [],
        selected_district: null,
        villages: [],
        selected_village: null,
        selectedData: [],
        data: [],
        confirmation: '',
        tabs: [1,2],
        selected_tab: 1,
        snackbar: false,
        snackbar_text: '',
        alert_password: false
    },
    mutations: {
        update_selected_data(state, value) {
            state.selectedData = value;
        },
        set_confirmation (state,v) {state.confirmation = v },
        set_common (state, v) {
            let name = v[0]
            let val = v[1]
            if (typeof(val) == "string")
                eval(`state.${name} = "${val}"`)
            else
                eval(`state.${name} = ${val}`)
        },
        update_respon (state, v){
            state.respon = v; 
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
        async selected(context) {
            context.commit("update_search_status", 1)
            try {
                let prm = {}
                prm.token = one_token()
                let resp = await api.search_selected(prm)
                console.log(prm);
                console.log(resp);
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    let pr = resp.data
                    context.commit("update_respon", resp.data.selectedData )
                    context.commit("update_search_status", 2)
                    context.commit("update_search_error_message", "")
                }
            } catch (e) {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
    },

    async save(context) {
        let prm = {
            selected_tables: context.state.selectedData
        }

        return context.dispatch("system/postme", {
            url: "systm/system/data_erase",
            prm: prm,
            callback: function(d) {
                return d
            }
        }, { root: true })
    },


    async save_selected(context) {
        context.commit("update_search_status", 1)
        try {
            let prm = {}
            prm.selectedData = context.state.selectedData;
            let resp = await api.save_selected(prm)
            if (resp.status != "OK") {
                context.commit("update_search_status", 3)
                context.commit("update_search_error_message", resp.message)
            } else {
                console.log(resp);
                let info = resp.message
                context.commit("update_search_status", 2)
                context.commit("update_search_error_message", "")
                context.commit('set_common', ['snackbar', true])
                context.commit('set_common', ['snackbar_text', info ])
            }
        } catch (e) {
            context.commit("update_search_status", 3)
            context.commit("update_search_error_message", e.message)
            console.log(e)
        }
},


    }
}