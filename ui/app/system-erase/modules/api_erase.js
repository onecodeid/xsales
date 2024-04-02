import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'systm/user/get_profile', prm)
}

export async function search_province(prm) {
    return ajaxPost(URL + 'master/province/search', prm)
}

export async function search_city(prm) {
    return ajaxPost(URL + 'master/city/search', prm)
}

export async function search_district(prm) {
    return ajaxPost(URL + 'master/district/search', prm)
}

export async function search_village(prm) {
    return ajaxPost(URL + 'master/kelurahan/search', prm)
}
export async function search_selected(prm){
    return ajaxPost(URL +   "selected/data/get_data", prm)
}
export async function save_selected(prm){
    return ajaxPost(URL + 'selected/data/save_data', prm)
}