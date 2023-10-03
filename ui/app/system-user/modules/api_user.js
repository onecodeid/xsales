import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search_groups(prm) {
    return ajaxPost(URL + 'systm/usergroup/search', prm)
}

export async function search_reports(prm) {
    return ajaxPost(URL + 'report/report/search_groups', prm)
}

export async function search_users(prm) {
    return ajaxPost(URL + 'systm/user/search', prm)
}

export async function search_menus(prm) {
    return ajaxPost(URL + 'systm/menu/search_all_id', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'systm/usergroup/save', prm)
}

export async function save_user(prm) {
    return ajaxPost(URL + 'systm/user/save', prm)
}

export async function check_user(prm) {
    return ajaxPost(URL + 'systm/user/check', prm)
}

export async function del(prm) {
    return ajaxPost(URL + 'systm/user/del', prm)
}