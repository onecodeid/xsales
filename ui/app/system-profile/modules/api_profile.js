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

export async function save_profile(prm) {
    return ajaxPost(URL + 'systm/user/save_profile', prm)
}

export async function save_password(prm) {
    return ajaxPost(URL + 'systm/user/save_password', prm)
}