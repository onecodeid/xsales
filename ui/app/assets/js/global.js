var URLx
var HOSTx
if (window.location.hostname == "localhost")
    HOSTx = "http://localhost/xsales/"
    // URLx = "http://localhost/erpadywater/api/"
else
    HOSTx = "https://" + window.location.hostname + "/"
    // URLx = "https://" + window.location.hostname + "/api/"
URLx = HOSTx + "api/"
export const URL = URLx
export const UPL_URL = "http://platform.zalfa.id/uploads/"
export
const HOST = HOSTx

// Replaced by system conf
export const PPN = 10

// Tab Const
export const TAB_01 = "TAB.01"
export const TAB_02 = "TAB.02"
export const TAB_03 = "TAB.03"

export const veryStartDate = "2023-01-01"
    // export const siteName = "Ady Water"

export function one_token() {
    let x = ''
    try {
        x = localStorage.getItem('token')
    } catch (error) {
        x = ''
    }

    return x
}

export async function ajaxPost(u, p, h) {
    try {
        if (h)
            var r = await axios.post(u, p, h)
        else
            var r = await axios.post(u, p)
        if (r.status != 200) {
            return {
                status: "ERR",
                message: r.statusText
            };
        }
        let d = r.data;
        return d;
    } catch (e) {
        return {
            status: "ERR",
            message: e.message
        };
    }
}

export function one_money(inp, format) {
    return numeral(inp).format(format ? format : '0,000')
}

export async function menu_group(prm) {
    return ajaxPost(URL + 'systm/menu/search_group', prm)
}

export function one_user() {
    return JSON.parse(localStorage.getItem('user'))
}

export function current_date() {
    try {
        let x = moment().format('YYYY-MM-DD')
        return x
    } catch (error) {
        return null
    }
}

export function lastmonth_date() {
    try {
        let x = moment().subtract(1, 'months').format('YYYY-MM-DD')
        return x
    } catch (error) {
        return null
    }
}

export function first_date() {
    try {
        let x = moment().startOf('month').format('YYYY-MM-DD')
        return x
    } catch (error) {
        return null
    }
}

export function page_sizes() {
    return [
        { size: "A4", orientation: "P", orientation_label: "Portrait", id: "A4P" },
        { size: "A5", orientation: "L", orientation_label: "Landscape", id: "A5L" }
    ]
}