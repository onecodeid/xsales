// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_disc.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',

        selected_disc: {}, // Mengganti 'affiliate' menjadi 'disc'
        dialog_new: false,

        disc_name: "", // Mengganti 'affiliate_name' menjadi 'disc_name'
        disc_code: "",
        disc_amount: 0 // Mengganti 'affiliate_number' menjadi 'disc_code'
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

        set_dialog_new(state, v) {
            state.dialog_new = v
        }
    },
    actions: {
        async save(context) {

            try {
                let prm = {
                    token: one_token(),
                    disc_name: context.state.disc_name, // Mengganti 'affiliate_name' menjadi 'disc_name'
                    disc_code: context.state.disc_code, // Mengganti 'affiliate_number' menjadi 'disc_code'
                    disc_amount: context.state.disc_amount
                }

                if (context.state.edit)
                    prm.disc_id = context.rootState.disc.selected_disc.disc_id // Mengganti 'affiliate' menjadi 'disc'

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")

                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.disc) // Mengganti 'affiliate' menjadi 'disc'
                        context.dispatch("disc/search", {}, { root: true }) // Mengganti 'affiliate' menjadi 'disc'
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_disc', { disc_id: resp.data }, { root: true }) // Mengganti 'affiliate' menjadi 'disc'
                        context.dispatch('item_new/search_disc', {}, { root: true }) // Mengganti 'affiliate' menjadi 'disc'
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        }
    }
}
