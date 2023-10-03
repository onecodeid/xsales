import { one_token, URL } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        URL: URL,
        search_status: 0,
        search_error_message: '',
        search: '',

        item_id: 0,
        item_code: 'ITEMKODE',
        item_name: 'Item Name',
        purchases: [],
        selected_purchase: null,

        dialog: false
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
            state[v[0]] = v[1]
        }
    },
    actions: {

    }
}