import { one_token } from "../../assets/js/global.js"

export default {
    namespaced: true,
    state: {
        disc: 0,
        discrp: 0,
        disctype: 'P',
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