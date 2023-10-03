import { URL, ajaxPost } from "./global.min.js"

export async function postme(aurl, prm, hdr) {
    if (!hdr) return ajaxPost(URL + aurl, prm)
    return ajaxPost(URL + aurl, prm, hdr)
}

export async function report_excel(URLx, prm) {
    return ajaxPost(URLx, prm)
}