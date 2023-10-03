// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import * as api from "./api_pack.js"
import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        edit: false,
        search_status: 0,
        search_error_message: '',
        search: '',
        
        selected_pack: {},
        dialog_new: false,

        units : [],
        selected_unit: {},

        pack_name: "",
        pack_code: "",
        pack_conversion: 1
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

        set_dialog_new (state, v) {
            state.dialog_new = v
        },

        set_units (state, v) {
            state.units = v
        },

        set_selected_unit (state, v) {
            state.selected_unit = v
        }
    },
    actions: {
        async save(context) {
            
            try {
                let prm = {token : one_token(),
                            pack_name: context.state.pack_name,
                            pack_code: context.state.pack_code,
                            pack_conversion: context.state.pack_conversion,
                            pack_unit: !!context.state.selected_unit?context.state.selected_unit.unit_id:0
                        }

                if (context.state.edit)
                        prm.pack_id = context.rootState.pack.selected_pack.pack_id

                let resp = await api.save(prm)
                if (resp.status != "OK") {
                    context.commit("update_search_status", 3)
                    context.commit("update_search_error_message", resp.message)
                } else {
                    context.commit("set_common", ["search_status", 2])
                    context.commit("update_search_error_message", "")
                    
                    context.commit("set_common", ["dialog_new", false])
                    if (!!context.rootState.pack)
                        context.dispatch("pack/search", {}, {root:true})
                    if (!!context.rootState.item_new) {
                        context.commit('item_new/set_selected_pack', {pack_id:resp.data}, {root:true})
                        context.dispatch('item_new/search_pack', {}, {root:true})
                    }
                    if (!!context.rootState.sales_new) {
                        context.dispatch('sales_new/search_pack', {}, {root:true})
                    }
                }
            } catch (e) {
                context.commit("set_common", ["search_city_status", 3])
                context.commit("update_search_error_message", e.message)
                console.log(e)
            }
        },

        async search_unit(context) {
            let prm = {
                search : '',
                page : 1,
                token : one_token()
            }

            context.dispatch("system/postme", {
                url:"master/unit/search",
                prm:prm,
                callback:function(d) {
                    context.commit("set_units", d.records)

                    if (!!context.state.selected_unit)
                        for (let u of d.records)
                            if (u.unit_id == context.state.selected_unit.unit_id)
                                context.commit('set_selected_unit', u)
                }
            }, {root:true})
        }
    }
}