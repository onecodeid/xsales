import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'systm/staff/search', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'systm/staff/save', prm)
}

export async function del(prm) {
    return ajaxPost(URL + 'systm/staff/del', prm)
}