import { URL, ajaxPost } from "../../assets/js/global.js"

export async function ipaymu_balance(prm) {
    return ajaxPost(URL + 'systm/ipaymu/balance_check', prm)
}

export async function admin_total_fee(prm) {
    return ajaxPost(URL + 'dashboard/dashboard/get_fee_total_by_admin', prm)
}

export async function omzet_per_product(prm) {
    return ajaxPost(URL + 'dashboard/dashboard/get_omzet_total_by_product', prm)
}

export async function profile(prm) {
    return ajaxPost(URL + 'systm/user/get_profile', prm)
}

export async function get_target_this_week(prm) {
    return ajaxPost(URL + 'dashboard/dashboard/get_target_this_week', prm)
}

export async function get_customer_total_new(prm) {
    return ajaxPost(URL + 'dashboard/dashboard/get_customer_total_new', prm)
}

export async function get_omzet_admin(prm) {
    return ajaxPost(URL + 'dashboard/dashboard/get_omzet_admin', prm)
}