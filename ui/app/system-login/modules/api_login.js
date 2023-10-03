import { URL, ajaxPost } from "../../assets/js/global.js"

export async function login(prm) {
    return ajaxPost(URL + 'systm/user/login', prm)
}