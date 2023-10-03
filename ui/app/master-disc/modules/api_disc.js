import { URL, ajaxPost } from "../../assets/js/global.js"

export async function search(prm) {
    return ajaxPost(URL + 'master/disc/search', prm) // Mengganti 'affiliate' menjadi 'disc'
}

export async function save(prm) {
    return ajaxPost(URL + 'master/disc/save', prm) // Mengganti 'affiliate' menjadi 'disc'
}

export async function del(prm) {
    return ajaxPost(URL + 'master/disc/del', prm) // Mengganti 'affiliate' menjadi 'disc'
}
