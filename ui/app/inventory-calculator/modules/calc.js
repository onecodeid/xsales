// 1 => LOADING
// 2 => DONE
// 3 => ERROR
import { one_token, veryStartDate } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        search_status: 0,
        search_error_message: '',
        search: '',
        query: '',

        current_page: 1,
        sdate: veryStartDate, // new Date().toISOString().substr(0, 10),
        edate: new Date().toISOString().substr(0, 10),

        c2_n: 3,
        c2_columns: []
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
    actions: {}
}