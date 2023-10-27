import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search_groups(prm) {
    return ajaxPost(URL + 'report/report/search_group_id', prm)
}

export async function search_admins(prm) {
    return ajaxPost(URL + 'report/report/search_admins', prm)
}

export async function search_staff(prm) {
    return ajaxPost(URL + 'systm/staff/search', prm)
}

export async function search_customer(prm) {
    return ajaxPost(URL + 'master/customer/search_autocomplete', prm)
}

export async function search_region(prm) {
    return ajaxPost(URL + 'admin/customerregion/search_autocomplete', prm)
}

export async function search_warehouse(prm) {
    return ajaxPost(URL + 'master/warehouse/search', prm)
}

export async function search_customer_level(prm) {
    return ajaxPost(URL + 'master/customerlevel/search', prm)
}

export async function search_province(prm) {
    return ajaxPost(URL + 'master/province/search', prm)
}

export async function search_city(prm) {
    return ajaxPost(URL + 'master/city/search', prm)
}

export async function search_expedition(prm) {
    return ajaxPost(URL + 'master/expedition/search', prm)
}

export async function search_item(prm) {
    return ajaxPost(URL + 'master/item/search', prm)
}

export async function search_month(prm) {
    return ajaxPost(URL + 'report/report/search_month', prm)
}