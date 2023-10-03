import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'inventory/assembly/search', prm)
}

export async function search_detail(prm) {
    return ajaxPost(URL + 'inventory/assemblydetail/search', prm)
}

export async function search_item(prm) {
    return ajaxPost(URL + 'inventory/stock/search_item_w_stock', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'inventory/assembly/save', prm)
}

export async function del(prm) {
    return ajaxPost(URL + 'inventory/assembly/del', prm)
}