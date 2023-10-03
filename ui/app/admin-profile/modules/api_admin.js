import { URL, ajaxPost } from "../../assets/js/global.js"

export async function ipaymu_balance(prm) {
    return ajaxPost(URL + 'systm/ipaymu/balance_check', prm)
}