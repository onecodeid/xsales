import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'master/category/search', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'master/category/save', prm)
}

export async function del(prm) {
    return ajaxPost(URL + 'master/category/del', prm)
}