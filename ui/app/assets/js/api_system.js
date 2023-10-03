import { URL, ajaxPost } from "./global.js"

export async function search_menu_group(prm) {
    return ajaxPost(URL + 'systm/menu/search_group', prm)
}

export async function get_notif_unread(prm) {
    return ajaxPost(URL + 'systm/notif/get_unread', prm)
}

export async function set_notif_read(prm) {
    return ajaxPost(URL + 'systm/notif/set_read', prm)
}