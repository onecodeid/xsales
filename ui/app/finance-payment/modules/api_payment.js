import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'trans/journal/search', prm)
}

export async function save(prm) {
    return ajaxPost(URL + 'trans/journal/save', prm)
}

export async function del(prm) {
    return ajaxPost(URL + 'trans/journal/del', prm)
}

// export async function search_unit(prm) {
//     return ajaxPost(URL + 'trans/unit/search', prm)
// }

// export async function search_level_price(prm) {
//     return ajaxPost(URL + 'trans/price/search_by_journal', prm)
// }