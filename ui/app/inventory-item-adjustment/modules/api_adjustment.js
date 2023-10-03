import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'inventory/adjust/search', prm)
}

export async function search_detail(prm) {
    return ajaxPost(URL + 'inventory/adjustdetail/search', prm)
}

export async function search_item(prm) {
    return ajaxPost(URL + 'inventory/stock/search_item_w_stock', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'inventory/adjust/save', prm)
}

// export async function del(prm) {
//     return ajaxPost(URL + 'master/customer/del', prm)
// }